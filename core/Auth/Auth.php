<?php
declare(strict_types=1);

namespace Core\Auth;

use Core\Database\DB;
use Core\Support\Session;

/**
 * სესიაზე დაფუძნებული ავტორიზაცია ერთი მომხმარებლების ცხრილისთვის (config/auth.php).
 * მომხმარებელი ყოველ მოთხოვნაზე ბაზიდან იკითხება, ამიტომ გათიშვა და პაროლის შეცვლა
 * მაშინვე მოქმედებს ყველა ღია სესიაზე.
 */
final class Auth
{
    private const LAST_ACTIVITY = '_auth_last';
    private const FINGERPRINT = '_auth_fp';

    private string $tableName;
    /** ბრჭყალებში, SQL-ში ჩასასმელად */
    private string $table;
    private string $sessionKey;
    private int $idleTimeout;

    /** @var array{table: string, cookie: string, lifetime: int}|null „დამიმახსოვრე“ (null — გამორთულია) */
    private ?array $remember;

    /** @var array<string, mixed>|false|null false — უკვე შემოწმდა და სტუმარია */
    private array|false|null $user = null;

    public function __construct(private readonly DB $db, array $config = [])
    {
        $this->tableName = $config['table'] ?? 'users';
        $this->table = DB::ident($this->tableName);
        $this->sessionKey = $config['session_key'] ?? '_auth_id';
        $this->idleTimeout = (int)($config['idle_timeout'] ?? 7200);
        $this->remember = isset($config['remember']['table'], $config['remember']['cookie']) ? [
            'table'    => (string)$config['remember']['table'],
            'cookie'   => (string)$config['remember']['cookie'],
            'lifetime' => (int)($config['remember']['lifetime'] ?? 30 * 86400),
        ] : null;
    }

    /** @return array<string, mixed>|null წარმატებისას მომხმარებელი (უკვე შესული) */
    public function attempt(string $email, string $password, bool $remember = false): ?array
    {
        $user = $this->db->first("SELECT * FROM {$this->table} WHERE email = ? AND is_active = 1", [strtolower(trim($email))]);

        // უცნობ email-ზეც ვამოწმებთ ნამდვილ hash-ს, რომ პასუხის დრომ არ გასცეს ანგარიშის არსებობა
        static $dummy = null;
        $hash = $user['password_hash'] ?? ($dummy ??= password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT));
        if (!password_verify($password, $hash) || $user === null) {
            return null;
        }

        if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
            $user['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
            $this->db->update($this->tableName, ['password_hash' => $user['password_hash']], 'id = ?', [$user['id']]);
        }

        $this->login($user);
        if ($remember) {
            $this->issueRememberToken($user);
        }
        return $user;
    }

    public function login(array $user): void
    {
        Session::regenerate();
        Session::forget('_csrf');
        Session::set($this->sessionKey, (int)$user['id']);
        Session::set(self::FINGERPRINT, self::fingerprint($user));
        Session::set(self::LAST_ACTIVITY, time());
        $this->user = $user;
    }

    /** მიმდინარე სესიის პაროლის ანაბეჭდის განახლება (საკუთარი პაროლის შეცვლის შემდეგ) */
    public function refresh(): void
    {
        $this->user = null;
        $id = Session::get($this->sessionKey);
        $user = $id ? $this->db->first("SELECT * FROM {$this->table} WHERE id = ?", [$id]) : null;
        if ($user !== null) {
            $this->login($user);
        }
    }

    /** სესიის დასრულება და ამ მოწყობილობის „დამიმახსოვრე“ token-ის გაუქმება */
    public function logout(): void
    {
        [$selector] = $this->rememberCookie() ?? [null];
        if ($selector !== null) {
            $this->db->delete($this->remember['table'], 'selector = ?', [$selector]);
        }
        $this->forgetRememberCookie();
        $this->endSession();
    }

    private function endSession(): void
    {
        $this->user = false;
        Session::invalidate();
    }

    /** @return array<string, mixed>|null */
    public function user(): ?array
    {
        if ($this->user === null) {
            $this->user = $this->resolve() ?? false;
        }
        return $this->user ?: null;
    }

    public function id(): ?int
    {
        return isset($this->user()['id']) ? (int)$this->user()['id'] : null;
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    private function resolve(): ?array
    {
        $id = Session::get($this->sessionKey);
        if (!is_int($id)) {
            return $this->resolveFromRememberCookie();
        }

        $last = (int)Session::get(self::LAST_ACTIVITY, 0);
        $user = $this->db->first("SELECT * FROM {$this->table} WHERE id = ? AND is_active = 1", [$id]);

        if ($user === null
            || time() - $last > $this->idleTimeout
            || !hash_equals((string)Session::get(self::FINGERPRINT, ''), self::fingerprint($user))) {
            // სესია დასრულდა; „დამიმახსოვრე“ token (თუ ვალიდურია) ახალ სესიას ხსნის
            $this->endSession();
            return $this->resolveFromRememberCookie();
        }

        Session::set(self::LAST_ACTIVITY, time());
        return $user;
    }

    // ---- „დამიმახსოვრე“ ----

    /** ახალი token: ბაზაში validator-ის sha256, cookie-ში selector:validator */
    private function issueRememberToken(array $user): void
    {
        if ($this->remember === null) {
            return;
        }
        $table = $this->remember['table'];
        // დრო PHP-დან (expires_at-იც PHP-ის date()-ით იწერება — MySQL-ის NOW() სხვა timezone-ში შეიძლება იყოს)
        $this->db->delete($table, 'user_id = ? AND expires_at < ?', [$user['id'], date('Y-m-d H:i:s')]);

        $selector = bin2hex(random_bytes(12));
        $validator = bin2hex(random_bytes(32));
        $expires = time() + $this->remember['lifetime'];
        $this->db->insert($table, [
            'user_id'        => (int)$user['id'],
            'selector'       => $selector,
            'validator_hash' => hash('sha256', $validator),
            'fingerprint'    => self::fingerprint($user),
            'expires_at'     => date('Y-m-d H:i:s', $expires),
        ]);
        $this->setRememberCookie($selector . ':' . $validator, $expires);
    }

    /** @return array<string, mixed>|null */
    private function resolveFromRememberCookie(): ?array
    {
        [$selector, $validator] = $this->rememberCookie() ?? [null, null];
        if ($selector === null) {
            return null;
        }

        $table = DB::ident($this->remember['table']);
        $token = $this->db->first("SELECT * FROM $table WHERE selector = ? AND expires_at > ?", [$selector, date('Y-m-d H:i:s')]);
        $user = $token ? $this->db->first("SELECT * FROM {$this->table} WHERE id = ? AND is_active = 1", [$token['user_id']]) : null;

        // ერთჯერადი: ძველი token ყოველთვის იშლება (ვალიდურზე — ახლით იცვლება)
        if ($token !== null) {
            $this->db->delete($this->remember['table'], 'id = ?', [$token['id']]);
        }
        if ($token === null || $user === null
            || !hash_equals((string)$token['validator_hash'], hash('sha256', $validator))
            || !hash_equals((string)$token['fingerprint'], self::fingerprint($user))) {
            $this->forgetRememberCookie();
            return null;
        }

        $this->login($user);
        $this->issueRememberToken($user);
        return $user;
    }

    /** @return array{0: string, 1: string}|null */
    private function rememberCookie(): ?array
    {
        if ($this->remember === null) {
            return null;
        }
        $value = $_COOKIE[$this->remember['cookie']] ?? null;
        if (!is_string($value) || !preg_match('/^([0-9a-f]{24}):([0-9a-f]{64})$/', $value, $m)) {
            return null;
        }
        return [$m[1], $m[2]];
    }

    private function setRememberCookie(string $value, int $expires): void
    {
        $_COOKIE[$this->remember['cookie']] = $value;
        if (!headers_sent()) {
            setcookie($this->remember['cookie'], $value, [
                'expires'  => $expires,
                'path'     => '/',
                'secure'   => (bool)session_get_cookie_params()['secure'],
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
    }

    private function forgetRememberCookie(): void
    {
        if ($this->remember === null || !isset($_COOKIE[$this->remember['cookie']])) {
            return;
        }
        unset($_COOKIE[$this->remember['cookie']]);
        if (!headers_sent()) {
            setcookie($this->remember['cookie'], '', [
                'expires'  => time() - 3600,
                'path'     => '/',
                'secure'   => (bool)session_get_cookie_params()['secure'],
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
    }

    /** პაროლის შეცვლისას იცვლება — ძველი სესიები ავტომატურად იხურება */
    private static function fingerprint(array $user): string
    {
        return hash('sha256', (string)$user['password_hash']);
    }
}
