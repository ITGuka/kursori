<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Campaign;
use App\Models\Language;
use Core\Mail\Mailer;
use Core\Support\Log;
use Core\View\View;
use Throwable;

/**
 * Newsletter-ის წერილები: გამოწერის დასტური და კამპანიის გაგზავნა პარტიებად.
 * ბმულები — absolute_url() (APP_URL), ენა — გამომწერის ენა.
 */
final class Newsletter
{
    public function __construct(
        private readonly Mailer $mailer,
        private readonly View $view,
        private readonly Campaign $campaigns,
        private readonly Language $languages,
    ) {
    }

    /** @throws Throwable SMTP შეცდომა */
    public function sendConfirmation(array $subscriber): void
    {
        $lang = $subscriber['lang'];
        $html = $this->view->render('emails/newsletter-confirm', [
            'lang' => $lang,
            'url'  => absolute_url($lang . '/newsletter/confirm/' . $subscriber['token']),
        ]);
        $this->mailer->send($subscriber['email'], t('newsletter.email_confirm_subject', ['site' => setting('general.site_name', $lang)], $lang), $html);
    }

    /** ტესტური წერილი ადმინის მისამართზე (გაუქმების ბმული — დემო) */
    public function sendTest(array $campaign, string $email): void
    {
        $lang = $campaign['lang'] !== '' ? $campaign['lang'] : $this->languages->defaultCode();
        $this->mailer->send($email, '[TEST] ' . $campaign['subject'], $this->campaignHtml($campaign, $lang, str_repeat('0', 64)));
    }

    /**
     * მომდევნო პარტია; ყოველი წერილი ცალკე (პერსონალური გაუქმების ბმული).
     * @return array{total: int, sent: int, failed: int, pending: int, done: bool}
     */
    public function sendBatch(int $campaignId, int $size = 20): array
    {
        $campaign = $this->campaigns->find($campaignId);
        if ($campaign !== null && $campaign['status'] === 'sending') {
            foreach ($this->campaigns->pending($campaignId, $size) as $row) {
                if ($row['subscriber_status'] !== 'active') {
                    // გაშვების შემდეგ გააუქმა — არ ეგზავნება
                    $this->campaigns->markSend($campaignId, (int)$row['subscriber_id'], false, 'unsubscribed');
                    continue;
                }
                try {
                    $unsubscribe = absolute_url($row['lang'] . '/newsletter/unsubscribe/' . $row['token']);
                    $this->mailer->send($row['email'], $campaign['subject'], $this->campaignHtml($campaign, $row['lang'], $row['token']), '', [
                        'List-Unsubscribe'      => '<' . $unsubscribe . '>',
                        'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
                    ]);
                    $this->campaigns->markSend($campaignId, (int)$row['subscriber_id'], true);
                } catch (Throwable $e) {
                    Log::error($e, ['campaign' => $campaignId, 'subscriber' => $row['subscriber_id']]);
                    $this->campaigns->markSend($campaignId, (int)$row['subscriber_id'], false, $e->getMessage());
                }
            }
        }

        $stats = $this->campaigns->stats($campaignId);
        if ($stats['pending'] === 0) {
            $this->campaigns->finish($campaignId);
        }
        return $stats + ['done' => $stats['pending'] === 0];
    }

    private function campaignHtml(array $campaign, string $lang, string $token): string
    {
        return $this->view->render('emails/newsletter-campaign', [
            'lang'           => $lang,
            'subject'        => $campaign['subject'],
            'body'           => $campaign['body'],
            'unsubscribeUrl' => absolute_url($lang . '/newsletter/unsubscribe/' . $token),
        ]);
    }
}
