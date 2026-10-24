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

    /** @var array<string, mixed>|false|null false — უკვე შემოწმდა და სტუმარია */
    private array|false|null $user = null;

    public function __construct(private readonly DB $db, array $config = [])
    {
        $this->tableName = $config['table'] ?? 'users';
        $this->table = DB::ident($this->tableName);
        $this->sessionKey = $config['session_key'] ?? '_auth_id';
        $this->idleTimeout = (int)($config['idle_timeout'] ?? 7200);
    }

    /** @return array<string, mixed>|null წარმატებისას მომხმარებელი (უკვე შესული) */
    public function attempt(string $email, string $password): ?array
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

    public function logout(): void
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
            return null;
        }

        $last = (int)Session::get(self::LAST_ACTIVITY, 0);
        $user = $this->db->first("SELECT * FROM {$this->table} WHERE id = ? AND is_active = 1", [$id]);

        if ($user === null
            || time() - $last > $this->idleTimeout
            || !hash_equals((string)Session::get(self::FINGERPRINT, ''), self::fingerprint($user))) {
            $this->logout();
            return null;
        }

        Session::set(self::LAST_ACTIVITY, time());
        return $user;
    }

    /** პაროლის შეცვლისას იცვლება — ძველი სესიები ავტომატურად იხურება */
    private static function fingerprint(array $user): string
    {
        return hash('sha256', (string)$user['password_hash']);
    }
}
