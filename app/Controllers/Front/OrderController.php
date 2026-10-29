<?php
declare(strict_types=1);

namespace App\Controllers\Front;

use App\Models\Design;
use App\Models\FormSubmission;
use App\Models\Language;
use App\Models\Package;
use Core\Http\HttpException;
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
 */
final class OrderController
{
    private const FORM = 'order';

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
        $definition = FormSubmission::forms()[self::FORM] ?? throw new HttpException(404);
        $back = FormController::backTo($request, $lang, 'form-' . self::FORM);

        if (trim($request->string((string)config('forms.honeypot', 'website'), '')) !== '') {
            return redirect($back)->with('_form', self::FORM)->with('_form_success', t('order.sent'));
        }

        $input = $this->input($request);
        $limit = (array)config('forms.rate_limit');
        $key = 'form:' . self::FORM . ':' . $request->ip();
        if ($this->limiter->tooManyAttempts($key, (int)$limit['max'])) {
            return redirect($back)->with('_form', self::FORM)->with('_form_error', t('form.too_many'))->withInput($input);
        }

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
            $this->mailer->send($to, 'ახალი შეკვეთა #' . $id . ': ' . $data['name'], $html, '', filter_var($data['email'], FILTER_VALIDATE_EMAIL) ? ['Reply-To' => $data['email']] : []);
        } catch (Throwable $e) {
            Log::error($e, ['order' => $id]);
        }
    }
}
