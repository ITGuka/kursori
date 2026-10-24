<?php
declare(strict_types=1);

namespace Core\Http;

class Response
{
    /** @param array<string, string> $headers */
    public function __construct(
        protected string $content = '',
        protected int $status = 200,
        protected array $headers = [],
    ) {
    }

    public static function html(string $content, int $status = 200, array $headers = []): static
    {
        return new static($content, $status, $headers + ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    public static function json(mixed $data, int $status = 200, array $headers = []): static
    {
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        return new static($json, $status, $headers + ['Content-Type' => 'application/json; charset=UTF-8']);
    }

    public static function redirect(string $url, int $status = 302): static
    {
        return new static('', $status, ['Location' => $url]);
    }

    public function header(string $name, string $value): static
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function status(): int
    {
        return $this->status;
    }

    public function content(): string
    {
        return $this->content;
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public function send(): void
    {
        if (!headers_sent()) {
            http_response_code($this->status);
            foreach ($this->headers as $name => $value) {
                header($name . ': ' . $value);
            }
        }
        echo $this->content;
    }
}
