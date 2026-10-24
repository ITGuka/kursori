<?php
declare(strict_types=1);

namespace App\Controllers\Front;

use App\Models\FormSubmission;
use Core\Http\HttpException;
use Core\Http\Request;
use Core\Http\Response;
use Core\Support\RateLimiter;
use Core\Support\Validator;

/**
 * საჯარო ფორმები (config/forms.php): POST /{lang}/forms/{form} → Inbox.
 * შედეგი ბრუნდება იმავე გვერდზე, ფორმასთან (#form-{form}); შეცდომები/შეტყობინება — მხოლოდ ამ ფორმაში (flash _form).
 */
final class FormController
{
    public function __construct(private readonly FormSubmission $submissions, private readonly RateLimiter $limiter)
    {
    }

    public function submit(Request $request, string $lang, string $form): Response
    {
        $definition = FormSubmission::forms()[$form] ?? throw new HttpException(404);
        $back = self::backTo($request, $lang, "form-$form");

        // honeypot შევსებულია → ბოტი: წარმატებას ვაჩვენებთ, არაფერს ვინახავთ
        if (trim((string)$request->input((string)config('forms.honeypot', 'website'), '')) !== '') {
            return redirect($back)->with('_form', $form)->with('_form_success', t('form.sent'));
        }

        $limit = (array)config('forms.rate_limit');
        $key = "form:$form:" . $request->ip();
        if ($this->limiter->tooManyAttempts($key, (int)$limit['max'])) {
            return redirect($back)->with('_form', $form)->with('_form_error', t('form.too_many'))
                ->withInput($this->input($request, $definition));
        }

        $data = $this->input($request, $definition);
        $validator = Validator::make(
            $data,
            array_map(fn ($field) => $field['rules'], $definition['fields']),
            array_map(fn ($field) => t($field['label']), $definition['fields']),
            self::messages(),
        );
        if ($validator->fails()) {
            return redirect($back)->with('_form', $form)->withErrors($validator->errors())->withInput($data);
        }

        $this->limiter->hit($key, (int)$limit['decay']);
        $this->submissions->create($form, $lang, $data, (string)$request->header('Referer', ''), $request->ip(), (string)$request->header('User-Agent', ''));
        return redirect($back)->with('_form', $form)->with('_form_success', t('form.sent'));
    }

    /** @return array<string, string> მხოლოდ ფორმის ველები, გაწმენდილი */
    private function input(Request $request, array $definition): array
    {
        $data = [];
        foreach ($definition['fields'] as $name => $field) {
            $value = $request->input($name, '');
            $value = is_string($value) ? str_replace("\r\n", "\n", trim($value)) : '';
            $data[$name] = (string)preg_replace(!empty($field['multiline']) ? '/[\x00-\x09\x0B-\x1F\x7F]/u' : '/[\x00-\x1F\x7F]/u', '', $value);
        }
        return $data;
    }

    /** ვალიდაციის შეტყობინებები მიმდინარე ენაზე (UI თარგმანები: validation.*) */
    public static function messages(): array
    {
        $messages = [];
        foreach (['required', 'email', 'max', 'regex'] as $rule) {
            $messages[$rule] = t("validation.$rule");
        }
        return $messages;
    }

    /** იმავე საიტის გვერდი, საიდანაც ფორმა გაიგზავნა (სხვა host-ზე — არა), + ღუზა */
    public static function backTo(Request $request, string $lang, string $anchor): string
    {
        $referer = (string)$request->header('Referer', '');
        $refererHost = (string)parse_url($referer, PHP_URL_HOST) . (($port = parse_url($referer, PHP_URL_PORT)) ? ":$port" : '');
        $url = $referer !== '' && $refererHost === (string)$request->header('Host', '') ? (string)preg_replace('/#.*$/', '', $referer) : url($lang);
        return $url . '#' . $anchor;
    }
}
