<?php
declare(strict_types=1);

namespace App\Controllers\Front;

use App\Models\Subscriber;
use App\Services\Newsletter;
use Core\Http\Request;
use Core\Http\Response;
use Core\Support\Log;
use Core\Support\RateLimiter;
use Core\Support\Validator;
use Throwable;

/**
 * Newsletter: გამოწერა (double opt-in), დასტური, გაუქმება.
 * გაუქმება GET-ით მხოლოდ ღილაკს აჩვენებს (ფოსტის სკანერებმა ბმულზე გადასვლით რომ არ გააუქმონ), POST — აუქმებს.
 */
final class NewsletterController
{
    public function __construct(
        private readonly Subscriber $subscribers,
        private readonly Newsletter $newsletter,
        private readonly RateLimiter $limiter,
    ) {
    }

    public function subscribe(Request $request, string $lang): Response
    {
        $back = FormController::backTo($request, $lang, 'form-newsletter');
        if (trim($request->string((string)config('forms.honeypot', 'website'), '')) !== '') {
            return redirect($back)->with('_form', 'newsletter')->with('_form_success', t('newsletter.check_email'));
        }

        $email = strtolower(trim($request->string('email', '')));
        $key = 'newsletter:' . $request->ip();
        if ($this->limiter->tooManyAttempts($key, (int)config('forms.rate_limit.max', 5))) {
            return redirect($back)->with('_form', 'newsletter')->with('_form_error', t('form.too_many'))->withInput(['email' => $email]);
        }
        $validator = Validator::make(['email' => $email], ['email' => 'required|email|max:190'], ['email' => t('form.email')], FormController::messages());
        if ($validator->fails()) {
            return redirect($back)->with('_form', 'newsletter')->withErrors($validator->errors())->withInput(['email' => $email]);
        }

        $this->limiter->hit($key, (int)config('forms.rate_limit.decay', 600));
        $result = $this->subscribers->request($email, $lang, $request->ip());
        if ($result['needs_confirmation']) {
            try {
                $this->newsletter->sendConfirmation($result['subscriber']);
            } catch (Throwable $e) {
                Log::error($e, ['subscriber' => $result['subscriber']['id']]);
                return redirect($back)->with('_form', 'newsletter')->with('_form_error', t('newsletter.error'))->withInput(['email' => $email]);
            }
        }
        // უკვე აქტიურსაც იგივე ტექსტი — მისამართის გამოწერის სტატუსი სხვისთვის არ ჩანს
        return redirect($back)->with('_form', 'newsletter')->with('_form_success', t('newsletter.check_email'));
    }

    public function confirm(string $lang, string $token): Response
    {
        $subscriber = $this->subscribers->findByToken($token);
        if ($subscriber === null) {
            return $this->message(t('newsletter.invalid_link'), 404);
        }
        if ($subscriber['status'] !== 'active') {
            $this->subscribers->confirm((int)$subscriber['id']);
        }
        return $this->message(t('newsletter.confirmed'));
    }

    public function unsubscribePage(string $lang, string $token): Response
    {
        $subscriber = $this->subscribers->findByToken($token);
        if ($subscriber === null) {
            return $this->message(t('newsletter.invalid_link'), 404);
        }
        if ($subscriber['status'] === 'unsubscribed') {
            return $this->message(t('newsletter.unsubscribed'));
        }
        return $this->message(t('newsletter.unsubscribe_text', ['email' => $subscriber['email']]), 200, [
            'action' => route('newsletter.unsubscribe.confirm', ['lang' => $lang, 'token' => $token]),
            'button' => t('newsletter.unsubscribe_button'),
        ]);
    }

    /** CSRF-ის გარეშე (config app.csrf_except) — ფოსტის კლიენტის one-click POST-იც აქ მოდის */
    public function unsubscribe(string $lang, string $token): Response
    {
        $subscriber = $this->subscribers->findByToken($token);
        if ($subscriber === null) {
            return $this->message(t('newsletter.invalid_link'), 404);
        }
        $this->subscribers->unsubscribe((int)$subscriber['id']);
        return $this->message(t('newsletter.unsubscribed'));
    }

    /** @param array{action: string, button: string}|null $form */
    private function message(string $text, int $status = 200, ?array $form = null): Response
    {
        return view('front/message', [
            'heading' => t('newsletter.title'),
            'text'    => $text,
            'form'    => $form,
            'meta'    => ['title' => t('newsletter.title') . ' · ' . setting('general.site_name'), 'noindex' => true],
        ], $status);
    }
}
