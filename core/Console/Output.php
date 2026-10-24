<?php
declare(strict_types=1);

namespace Core\Console;

final class Output
{
    private bool $colors;

    public function __construct()
    {
        $this->colors = function_exists('sapi_windows_vt100_support')
            ? @sapi_windows_vt100_support(STDOUT, true)
            : stream_isatty(STDOUT);
    }

    public function line(string $text = ''): void
    {
        fwrite(STDOUT, $text . PHP_EOL);
    }

    public function info(string $text): void
    {
        $this->line($this->color($text, '32'));
    }

    public function warn(string $text): void
    {
        $this->line($this->color($text, '33'));
    }

    public function error(string $text): void
    {
        fwrite(STDERR, $this->color($text, '31') . PHP_EOL);
    }

    public function muted(string $text): string
    {
        return $this->color($text, '90');
    }

    public function highlight(string $text): string
    {
        return $this->color($text, '36');
    }

    /** @param list<array<int, string>> $rows */
    public function table(array $headers, array $rows): void
    {
        $widths = array_map('mb_strlen', $headers);
        foreach ($rows as $row) {
            foreach (array_values($row) as $i => $cell) {
                $widths[$i] = max($widths[$i] ?? 0, mb_strlen((string)$cell));
            }
        }

        $format = fn (array $cells) => implode('  ', array_map(
            fn ($cell, $i) => (string)$cell . str_repeat(' ', $widths[$i] - mb_strlen((string)$cell)),
            array_values($cells),
            array_keys(array_values($cells)),
        ));

        $this->line($this->highlight($format($headers)));
        foreach ($rows as $row) {
            $this->line($format($row));
        }
    }

    private function color(string $text, string $code): string
    {
        return $this->colors ? "\033[{$code}m{$text}\033[0m" : $text;
    }
}
