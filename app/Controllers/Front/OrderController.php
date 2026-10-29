<?php
declare(strict_types=1);

namespace App\Controllers\Front;

use App\Models\Design;
use App\Models\FormSubmission;
use App\Models\Language;
use App\Models\Package;
use App\Services\CustomOrder;
use Core\Http\HttpException;
use Core\Media\UploadException;
use Core\Http\Request;
use Core\Http\Response;
use Core\Mail\Mailer;
use Core\Support\Log;
use Core\Support\RateLimiter;
use Core\Support\Validator;
use Core\View\View;
use Throwable;

/**
 * საიტის შეკვეთა (order_form ბლოკი): POST /{lang}/order → Inbox (ფორმა „order“) + წერილი ადმინს.
 * პაკეტი, დარგი და დიზაინი მოწმდება ბაზით; Inbox-ში ინახება მათი სახელები ძირითად ენაზე და პაკეტის ფასი (შეკვეთის მომენტისთვის).
 * „ინდივიდუალური“ (package = custom) — ბრიფი (App\Services\CustomOrder), Inbox-ში ფორმა „custom“, + დადასტურება კლიენტს.
 */
final class OrderController
{
    private const FORM = 'order';
    /** package = custom → ინდივიდუალური შეკვეთა (config/forms.php → custom) */
    public const CUSTOM = 'custom';

    public function __construct(
        private readonly FormSubmission $submissions,
        private readonly Design $designs,
        private readonly Package $packages,
        private readonly Language $languages,
        private readonly RateLimiter $limiter,
        private readonly Mailer $mailer,
        private readonly View $view,
    ) {
    }

    public function submit(Request $request, string $lang): Response
    {
        isset(FormSubmission::forms()[self::FORM]) || throw new HttpException(404);
        $back = FormController::backTo($request, $lang, 'form-' . self::FORM);

        if (trim($request->string((string)config('forms.honeypot', 'website'), '')) !== '') {
            return redirect($back)->with('_form', self::FORM)->with('_form_success', t('order.sent'));
        }

        // ინდივიდუალური (package = custom) — ბრიფი, Inbox-ში ცალკე ფორმად („custom“)
        $custom = $request->string('package', '') === self::CUSTOM;
        $input = $custom ? ['package' => self::CUSTOM] + CustomOrder::input($request) : $this->input($request);
        $limit = (array)config('forms.rate_limit');
        $key = 'form:' . self::FORM . ':' . $request->ip();
        if ($this->limiter->tooManyAttempts($key, (int)$limit['max'])) {
            return redirect($back)->with('_form', self::FORM)->with('_form_error', t('form.too_many'))->withInput($input);
        }
        if ($custom) {
            return $this->custom($request, $lang, $back, $input, $key, (int)$limit['decay']);
        }

        $definition = FormSubmission::forms()[self::FORM];
        $fields = $definition['fields'];
        $validator = Validator::make(
            $input,
            array_map(fn ($field) => array_values(array_filter(is_array($field['rules']) ? $field['rules'] : explode('|', (string)$field['rules']))), $fields),
            array_map(fn ($field) => t($field['label']), $fields),
            FormController::messages(),
        );
        $errors = $validator->errors();

        // პაკეტი სავალდებულოა; დარგი — ამ პაკეტისა; დიზაინი — ამ პაკეტში (პაკეტზე ან მის დარგზე მიბმული)
        $default = $this->languages->defaultCode();
        $package = array_column($this->packages->published($default, $default), null, 'code')[$input['package']] ?? null;
        $categories = array_column($package['children'] ?? [], null, 'id');
        $design = array_column($this->designs->published($default, $default), null, 'id')[(int)$input['design']] ?? null;
        if ($package === null) {
            $errors['package'] = t('order.package_required');
        } elseif ($input['category'] !== '' && !isset($categories[(int)$input['category']])) {
            $errors['category'] = t('validation.in');
        }
        if ($design === null || ($package !== null && array_intersect($design['categories'], $package['ids']) === [])) {
            $errors['design'] = t('order.design_required');
        }
        foreach (['has_domain', 'has_hosting'] as $name) {
            if ($input[$name] !== 'yes') {
                $input[$name] = 'no';
            }
        }
        if ($errors !== []) {
            return redirect($back)->with('_form', self::FORM)->withErrors($errors)->withInput($input);
        }

        $this->limiter->hit($key, (int)$limit['decay']);
        $data = [
            'package'  => $package['name'] . ($package['price'] !== '' ? ' — ' . $package['price'] : ''),
            'category' => $input['category'] !== '' ? $categories[(int)$input['category']]['name'] : '',
            'design'   => $design['title'] . ' (#' . $design['id'] . ')',
        ] + $input;
        $data = array_merge(array_fill_keys(array_keys($fields), ''), array_intersect_key($data, $fields));
        $id = $this->submissions->create(self::FORM, $lang, $data, (string)$request->header('Referer', ''), $request->ip(), (string)$request->header('User-Agent', ''));
        $this->notify($id, $data, $lang, $definition);

        return redirect($back)->with('_form', self::FORM)->with('_form_success', t('order.sent'));
    }

    /**
     * ინდივიდუალური შეკვეთა (ბრიფი): ვალიდაცია პროექტის მიხედვით → ფაილი storage/orders-ში → Inbox → წერილები ადმინსა და კლიენტს.
     * ფაილი ინახება მხოლოდ ვალიდაციის შემდეგ (შეცდომისას ობოლი ფაილი არ რჩება) — შეცდომის შემდეგ თავიდან უნდა აირჩიონ.
     */
    private function custom(Request $request, string $lang, string $back, array $input, string $key, int $decay): Response
    {
        $definition = CustomOrder::definition();
        ['data' => $data, 'errors' => $errors] = CustomOrder::validate($input, $this->languages->defaultCode());
        if ($errors === []) {
            try {
                $attachment = CustomOrder::storeAttachment($request->file('attachment'));
                if ($attachment !== null) {
                    $data['attachment'] = $attachment;
                }
            } catch (UploadException $e) {
                $errors['attachment'] = $e->getMessage();
            }
        }
        if ($errors !== []) {
            return redirect($back)->with('_form', self::FORM)->withErrors($errors)->withInput($input);
        }

        $this->limiter->hit($key, $decay);
        $id = $this->submissions->create(CustomOrder::FORM, $lang, $data, (string)$request->header('Referer', ''), $request->ip(), (string)$request->header('User-Agent', ''));
        $this->notify($id, $data, $lang, $definition);
        $this->confirm($id, $data, $lang, $definition);

        return redirect($back)->with('_form', self::FORM)->with('_form_success', t('custom.sent'));
    }

    /** კლიენტს — დადასტურება ბრიფის ასლით მის ენაზე. ვერ გაიგზავნა → მხოლოდ ლოგი */
    private function confirm(int $id, array $data, string $lang, array $definition): void
    {
        if (empty($definition['confirm']) || !filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
            return;
        }
        try {
            $html = $this->view->render('emails/order-client', ['lang' => $lang, 'data' => $data, 'definition' => $definition, 'id' => $id]);
            $reply = trim((string)setting('orders.notify_email')) ?: trim((string)setting('contact.email'));
            $this->mailer->send($data['email'], t('custom.confirm_subject', ['site' => (string)setting('general.site_name', $lang), 'id' => $id], $lang), $html, '',
                filter_var($reply, FILTER_VALIDATE_EMAIL) ? ['Reply-To' => $reply] : []);
        } catch (Throwable $e) {
            Log::error($e, ['order' => $id, 'mail' => 'client']);
        }
    }

    /** @return array<string, string> */
    private function input(Request $request): array
    {
        $data = [];
        foreach (['name', 'phone', 'email', 'package', 'category', 'design', 'description', 'has_domain', 'has_hosting'] as $name) {
            $value = $request->input($name, '');
            $value = is_string($value) ? str_replace("\r\n", "\n", trim($value)) : '';
            $data[$name] = (string)preg_replace($name === 'description' ? '/[\x00-\x09\x0B-\x1F\x7F]/u' : '/[\x00-\x1F\x7F]/u', '', $value);
        }
        return $data;
    }

    /** წერილი ადმინს (პარამეტრები → შეკვეთები; ცარიელი → კონტაქტის ელ-ფოსტა). ვერ გაიგზავნა → მხოლოდ ლოგი, შეკვეთა Inbox-შია */
    private function notify(int $id, array $data, string $lang, array $definition): void
    {
        $to = trim((string)setting('orders.notify_email')) ?: trim((string)setting('contact.email'));
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return;
        }
        $default = $this->languages->defaultCode();
        try {
            $html = $this->view->render('emails/order-admin', [
                'lang'       => $default,
                'data'       => $data,
                'definition' => $definition,
                'orderLang'  => $lang,
                'url'        => absolute_url('admin/inbox/' . $id),
            ]);
            $this->mailer->send($to, $definition['label'] . ' #' . $id . ': ' . $data['name'], $html, '', filter_var($data['email'], FILTER_VALIDATE_EMAIL) ? ['Reply-To' => $data['email']] : []);
        } catch (Throwable $e) {
            Log::error($e, ['order' => $id]);
        }
    }
}
