<?php
declare(strict_types=1);

namespace Core\Http;

use Core\Support\Session;

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

    /** flash მონაცემი შემდეგი მოთხოვნისთვის (redirect-ის შემდეგ) */
    public function with(string $key, mixed $value): static
    {
        Session::flash($key, $value);
        return $this;
    }

    /** @param array<string, string> $errors ველი => შეცდომა; იკითხება errors() helper-ით */
    public function withErrors(array $errors): static
    {
        return $this->with('_errors', $errors);
    }

    /** ფორმის ველები old() helper-ისთვის; პაროლები აქ არ უნდა გადაეცეს */
    public function withInput(array $input): static
    {
        return $this->with('_old', $input);
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
            // სრული status line: Apache არასტანდარტულ კოდებს (მაგ. 419) სხვაგვარად 500-ად აქცევს
            $protocol = $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1';
            header(sprintf('%s %d %s', $protocol, $this->status, HttpException::reason($this->status, $this->status < 400 ? 'OK' : 'Error')), true, $this->status);
            foreach ($this->headers as $name => $value) {
                header($name . ': ' . $value);
            }
        }
        echo $this->content;
    }
}
