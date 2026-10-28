<?php
declare(strict_types=1);

namespace App\Services\News;

use RuntimeException;

/**
 * dev.to (Forem) API v1: სტატიების სია ტეგით და სრული სტატია. მოთხოვნებს შორის პაუზა და 429-ზე ხელახალი ცდა —
 * გასაღების გარეშე API ~30 მოთხოვნის შემდეგ ზღუდავს.
 */
final class DevToClient
{
    private const BASE = 'https://dev.to/api';
    private const PAUSE_MS = 1000;
    private const RETRIES = 2;

    private float $lastRequestAt = 0.0;

    public function __construct(private readonly string $apiKey = '')
    {
    }

    /**
     * ტეგის ყველაზე პოპულარული სტატიები ბოლო $topDays დღეში (body-ს გარეშე).
     * @return list<array<string, mixed>>
     */
    public function articles(string $tag, int $topDays, int $perPage = 30): array
    {
        $query = http_build_query(['tag' => $tag, 'top' => max(1, $topDays), 'per_page' => min(1000, max(1, $perPage))]);
        return array_values(array_filter($this->get('/articles?' . $query), 'is_array'));
    }

    /** @return array<string, mixed> სრული სტატია (body_html, tags მასივი) */
    public function article(int $id): array
    {
        return $this->get('/articles/' . $id);
    }

    private function get(string $path): array
    {
        if (!function_exists('curl_init')) {
            throw new RuntimeException('PHP curl extension is required for dev.to import.');
        }
        $headers = ['Accept: application/vnd.forem.api-v1+json', 'User-Agent: KURSORI/1.0 (+' . config('app.url') . ')'];
        if ($this->apiKey !== '') {
            $headers[] = 'api-key: ' . $this->apiKey;
        }

        for ($attempt = 0; ; $attempt++) {
            $this->throttle();
            $ch = curl_init(self::BASE . $path);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_HTTPHEADER     => $headers,
                CURLOPT_HEADER         => true,
            ]);
            $raw = curl_exec($ch);
            $error = curl_error($ch);
            $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $headerSize = (int)curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            curl_close($ch);

            if ($raw === false) {
                throw new RuntimeException("dev.to request failed: $error");
            }
            if ($status === 429 && $attempt < self::RETRIES) {
                $retryAfter = preg_match('/^retry-after:\s*(\d+)/mi', substr($raw, 0, $headerSize), $m) ? (int)$m[1] : 30;
                sleep(min(60, max(5, $retryAfter)));
                continue;
            }
            if ($status !== 200) {
                throw new RuntimeException("dev.to responded $status for $path");
            }
            $data = json_decode(substr($raw, $headerSize), true);
            if (!is_array($data)) {
                throw new RuntimeException("dev.to returned invalid JSON for $path");
            }
            return $data;
        }
    }

    private function throttle(): void
    {
        $wait = $this->lastRequestAt + self::PAUSE_MS / 1000 - microtime(true);
        if ($wait > 0) {
            usleep((int)($wait * 1_000_000));
        }
        $this->lastRequestAt = microtime(true);
    }
}
