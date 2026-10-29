<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\NewsItem;
use App\Services\News\NewsPublisher;
use Core\Database\DB;

/**
 * ჯობის გაშვებამდე შემოწმება (config/schedule.php → check): null — სამუშაო არის, სტრიქონი — გამოტოვების მიზეზი.
 * იაფი query-ებია — ახალი PHP პროცესი და ჟურნალის ჩანაწერი მხოლოდ საჭიროებისას იქმნება.
 */
final class JobChecks
{
    public function __construct(private readonly DB $db, private readonly NewsItem $news)
    {
    }

    public function reason(string $command): ?string
    {
        $method = (string)(config('schedule.commands')[$command]['check'] ?? '');
        return $method !== '' && method_exists($this, $method) ? $this->{$method}() : null;
    }

    private function newsFetch(): ?string
    {
        if ((int)$this->db->value('SELECT COUNT(*) FROM news_feeds WHERE is_active = 1') === 0) {
            return 'აქტიური feed არ არის.';
        }
        // დღეს მეტი აღარ გამოქვეყნდება — ახალს მეორე დღის წამოღება მოიტანს (უფრო ახალს)
        return $this->dailyLimitReached();
    }

    private function newsPublish(): ?string
    {
        if ($this->news->approvedIds(1) === []) {
            return 'გამოსაქვეყნებელი (დამტკიცებული) სიახლე არ არის.';
        }
        return $this->dailyLimitReached();
    }

    private function dailyLimitReached(): ?string
    {
        $limit = (int)setting('news.daily_limit');
        return $limit > 0 && $this->news->publishedToday() >= $limit ? "დღიური ლიმიტი ($limit) ამოწურულია." : null;
    }

    private function newsTranslate(): ?string
    {
        if (setting('news.translate') !== true || (string)setting('news.claude_api_key') === '') {
            return 'თარგმანი გამორთულია ან Claude API გასაღები არ არის.';
        }
        return $this->news->untranslatedIds(NewsPublisher::TRANSLATION_LANG, 1) === [] ? 'უთარგმნელი სიახლე არ არის.' : null;
    }

    private function newsletterSend(): ?string
    {
        return (int)$this->db->value("SELECT COUNT(*) FROM newsletter_campaigns WHERE status = 'sending'") === 0 ? 'გასაგზავნი კამპანია არ არის.' : null;
    }
}
