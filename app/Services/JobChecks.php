<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\NewsFeed;
use App\Models\NewsItem;
use Core\Database\DB;

/**
 * ჯობის გაშვებამდე შემოწმება (config/schedule.php → check): null — სამუშაო არის, სტრიქონი — გამოტოვების მიზეზი.
 * იაფი query-ებია — ახალი PHP პროცესი და ჟურნალის ჩანაწერი მხოლოდ საჭიროებისას იქმნება.
 */
final class JobChecks
{
    public function __construct(private readonly DB $db, private readonly NewsFeed $feeds, private readonly NewsItem $news)
    {
    }

    public function reason(string $command): ?string
    {
        $method = (string)(config('schedule.commands')[$command]['check'] ?? '');
        return $method !== '' && method_exists($this, $method) ? $this->{$method}() : null;
    }

    /** dev.to-ს არ მივმართავთ, თუ ყველა feed-ის კატეგორიაში დღეს უკვე გამოქვეყნდა (კატეგორიაში დღეში ერთი) */
    private function newsRun(): ?string
    {
        $categories = $this->feeds->activeCategories();
        if ($categories === [] && !$this->feeds->hasActiveWithoutCategory()) {
            return 'აქტიური feed არ არის.';
        }
        if (!$this->feeds->hasActiveWithoutCategory() && array_diff($categories, $this->news->categoriesPublishedToday()) === []) {
            return 'ყველა კატეგორიაში დღეს სიახლე უკვე გამოქვეყნდა.';
        }
        return null;
    }

    private function newsletterSend(): ?string
    {
        return (int)$this->db->value("SELECT COUNT(*) FROM newsletter_campaigns WHERE status = 'sending'") === 0 ? 'გასაგზავნი კამპანია არ არის.' : null;
    }
}
