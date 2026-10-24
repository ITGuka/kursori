<?php
declare(strict_types=1);

namespace Core\Http;

use RuntimeException;

final class HttpException extends RuntimeException
{
    public function __construct(
        private readonly int $status,
        string $message = '',
        private readonly array $headers = [],
    ) {
        parent::__construct($message !== '' ? $message : self::reason($status), $status);
    }

    public function status(): int
    {
        return $this->status;
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public static function reason(int $status): string
    {
        return match ($status) {
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            419 => 'Page Expired',
            422 => 'Unprocessable Content',
            429 => 'Too Many Requests',
            503 => 'Service Unavailable',
            default => 'Server Error',
        };
    }
}
