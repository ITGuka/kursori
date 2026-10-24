<?php
declare(strict_types=1);

namespace Core\Support;

use Throwable;

/**
 * ფაილური ლოგი: storage/logs/app-YYYY-MM-DD.log
 */
final class Log
{
    public static function error(string|Throwable $message, array $context = []): void
    {
        self::write('ERROR', $message, $context);
    }

    public static function info(string $message, array $context = []): void
    {
        self::write('INFO', $message, $context);
    }

    private static function write(string $level, string|Throwable $message, array $context): void
    {
        if ($message instanceof Throwable) {
            $context += ['file' => $message->getFile() . ':' . $message->getLine(), 'trace' => $message->getTraceAsString()];
            $message = get_class($message) . ': ' . $message->getMessage();
        }

        $line = sprintf("[%s] %s: %s", date('Y-m-d H:i:s'), $level, $message);
        if ($context !== []) {
            $line .= ' ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        $dir = app()->basePath('storage/logs');
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        @file_put_contents($dir . DIRECTORY_SEPARATOR . 'app-' . date('Y-m-d') . '.log', $line . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}
