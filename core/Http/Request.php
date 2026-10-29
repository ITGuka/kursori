<?php
declare(strict_types=1);

namespace Core\Http;

final class Request
{
    /** @var array<string, string> */
    private array $routeParams = [];
    private ?string $routeName = null;

    public function __construct(
        private readonly string $method,
        private readonly string $path,
        private readonly array $query = [],
        private readonly array $body = [],
        private readonly array $files = [],
        private readonly array $server = [],
        private readonly array $cookies = [],
        private readonly string $basePath = '',
    ) {
    }

    public static function capture(): self
    {
        $server = $_SERVER;
        $method = strtoupper($server['REQUEST_METHOD'] ?? 'GET');
        $body = $_POST;

        $contentType = (string)($server['CONTENT_TYPE'] ?? '');
        if (str_contains($contentType, 'application/json')) {
            $json = json_decode((string)file_get_contents('php://input'), true);
            $body = is_array($json) ? $json : [];
        } elseif ($method !== 'POST' && str_contains($contentType, 'application/x-www-form-urlencoded')) {
            // PHP $_POST-ს მხოლოდ POST-ზე ავსებს; ნამდვილი PUT/PATCH/DELETE-ის body ხელით
            parse_str((string)file_get_contents('php://input'), $body);
        }

        // HTML ფორმები მხოლოდ GET/POST-ს აგზავნიან: <input name="_method" value="DELETE">
        if ($method === 'POST' && isset($body['_method']) && is_string($body['_method'])) {
            $spoofed = strtoupper((string)$body['_method']);
            if (in_array($spoofed, ['PUT', 'PATCH', 'DELETE'], true)) {
                $method = $spoofed;
            }
        }

        $uri = rawurldecode((string)(parse_url($server['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/'));
        $basePath = self::detectBasePath($server, $uri);
        $path = '/' . trim(substr($uri, strlen($basePath)), '/');

        return new self($method, $path, $_GET, $body, $_FILES, $server, $_COOKIE, $basePath);
    }

    /**
     * public/index.php-ის საქაღალდე URL-ში. vhost-ზე ეს "" არის; localhost/KURSORI/-ზე
     * root .htaccess გადაამისამართებს public/-ში, ამიტომ URL-ში "/public" აღარ ჩანს.
     */
    private static function detectBasePath(array $server, string $uri): string
    {
        $scriptDir = rtrim(str_replace('\\', '/', dirname($server['SCRIPT_NAME'] ?? '/')), '/');

        foreach ([$scriptDir, (string)preg_replace('#/public$#', '', $scriptDir)] as $candidate) {
            if ($candidate === '' || $uri === $candidate || str_starts_with($uri, $candidate . '/')) {
                return $candidate;
            }
        }
        return '';
    }

    public function method(): string
    {
        return $this->method;
    }

    public function isMethod(string $method): bool
    {
        return $this->method === strtoupper($method);
    }

    /** ბილიკი base path-ის გარეშე, მაგ. "/ka/about" */
    public function path(): string
    {
        return $this->path;
    }

    public function basePath(): string
    {
        return $this->basePath;
    }

    public function query(?string $key = null, mixed $default = null): mixed
    {
        return $key === null ? $this->query : ($this->query[$key] ?? $default);
    }

    /** POST/JSON body, შემდეგ query string */
    public function input(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->body + $this->query;
        }
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }

    /** სტრიქონი input()-იდან; მასივი (მაგ. ?q[]=1) ან სხვა არასკალარული → $default */
    public function string(string $key, string $default = ''): string
    {
        $value = $this->input($key, $default);
        return is_scalar($value) ? (string)$value : $default;
    }

    public function has(string $key): bool
    {
        return isset($this->body[$key]) || isset($this->query[$key]);
    }

    public function only(array $keys): array
    {
        return array_intersect_key($this->input(), array_flip($keys));
    }

    public function file(string $key): ?array
    {
        $file = $this->files[$key] ?? null;
        return is_array($file) && ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE ? $file : null;
    }

    public function cookie(string $key, mixed $default = null): mixed
    {
        return $this->cookies[$key] ?? $default;
    }

    public function header(string $name, mixed $default = null): mixed
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return $this->server[$key] ?? $default;
    }

    public function server(string $key, mixed $default = null): mixed
    {
        return $this->server[$key] ?? $default;
    }

    /**
     * ვიზიტორის IP. production Cloudflare-ის უკანაა: მოთხოვნა Cloudflare-ის IP-დანაა → ნამდვილი IP CF-Connecting-IP-შია.
     * სათაურს ვენდობით მხოლოდ მაშინ, როცა REMOTE_ADDR Cloudflare-ის დიაპაზონშია (სხვაგან მისი გაყალბება შეიძლება).
     */
    public function ip(): string
    {
        $remote = (string)($this->server['REMOTE_ADDR'] ?? '0.0.0.0');
        $client = trim((string)($this->server['HTTP_CF_CONNECTING_IP'] ?? ''));
        if ($client !== '' && filter_var($client, FILTER_VALIDATE_IP) && self::isCloudflare($remote)) {
            return $client;
        }
        return $remote;
    }

    /** Cloudflare-ის IP დიაპაზონები: https://www.cloudflare.com/ips/ */
    private const CLOUDFLARE = [
        '173.245.48.0/20', '103.21.244.0/22', '103.22.200.0/22', '103.31.4.0/22', '141.101.64.0/18', '108.162.192.0/18',
        '190.93.240.0/20', '188.114.96.0/20', '197.234.240.0/22', '198.41.128.0/17', '162.158.0.0/15', '104.16.0.0/13',
        '104.24.0.0/14', '172.64.0.0/13', '131.0.72.0/22',
        '2400:cb00::/32', '2606:4700::/32', '2803:f800::/32', '2405:b500::/32', '2405:8100::/32', '2a06:98c0::/29', '2c0f:f248::/32',
    ];

    private static function isCloudflare(string $ip): bool
    {
        $address = @inet_pton($ip);
        if ($address === false) {
            return false;
        }
        foreach (self::CLOUDFLARE as $range) {
            [$subnet, $bits] = explode('/', $range);
            $network = inet_pton($subnet);
            if (strlen($network) !== strlen($address)) {
                continue;
            }
            $bytes = intdiv((int)$bits, 8);
            $rest = (int)$bits % 8;
            if (strncmp($address, $network, $bytes) !== 0) {
                continue;
            }
            if ($rest === 0 || (ord($address[$bytes]) >> (8 - $rest)) === (ord($network[$bytes]) >> (8 - $rest))) {
                return true;
            }
        }
        return false;
    }

    public function isSecure(): bool
    {
        return ($this->server['HTTPS'] ?? 'off') !== 'off';
    }

    public function isAjax(): bool
    {
        return $this->header('X-Requested-With') === 'XMLHttpRequest';
    }

    public function wantsJson(): bool
    {
        return $this->isAjax() || str_contains((string)$this->header('Accept', ''), 'application/json');
    }

    public function setRoute(?string $name, array $params): void
    {
        $this->routeName = $name;
        $this->routeParams = $params;
    }

    /** მიმდინარე route-ის სახელი, მაგ. "admin.users.edit" */
    public function routeName(): ?string
    {
        return $this->routeName;
    }

    public function param(string $key, mixed $default = null): mixed
    {
        return $this->routeParams[$key] ?? $default;
    }

    public function params(): array
    {
        return $this->routeParams;
    }
}
