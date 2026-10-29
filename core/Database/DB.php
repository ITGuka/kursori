<?php
declare(strict_types=1);

namespace Core\Database;

use InvalidArgumentException;
use PDO;
use PDOException;
use PDOStatement;
use Throwable;

/**
 * PDO-ს თხელი გარსი: კავშირი იხსნება პირველ query-ზე, ყველა მნიშვნელობა — prepared statement-ით.
 */
final class DB
{
    private ?PDO $pdo = null;

    public function __construct(private readonly array $config)
    {
    }

    public function pdo(): PDO
    {
        if ($this->pdo !== null) {
            return $this->pdo;
        }

        $c = $this->config;
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $c['host'] ?? 'localhost',
            (int)($c['port'] ?? 3306),
            $c['database'] ?? '',
            $c['charset'] ?? 'utf8mb4',
        );
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        // MySQL სესიის დრო PHP-ის დროის სარტყელს ემთხვევა (მაგ. "+04:00")
        if (preg_match('/^[+-]\d{2}:\d{2}$/', (string)($c['timezone'] ?? ''))) {
            $options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET time_zone = '{$c['timezone']}'";
        }

        return $this->pdo = new PDO($dsn, (string)($c['username'] ?? ''), (string)($c['password'] ?? ''), $options);
    }

    public function query(string $sql, array $bindings = []): PDOStatement
    {
        try {
            $statement = $this->pdo()->prepare($sql);
            $statement->execute($bindings);
            return $statement;
        } catch (PDOException $e) {
            // გრძელი გარე მოთხოვნის (მაგ. AI თარგმანი) შემდეგ სერვერი უმოქმედო კავშირს ხურავს (wait_timeout):
            // 2006 „server has gone away“, 2013 „lost connection“, 4031 (MySQL 8: უმოქმედობით გათიშვა) —
            // ხელახლა დაკავშირება და ერთი ცდა (ტრანზაქციის გარეთ)
            if (!in_array((int)($e->errorInfo[1] ?? 0), [2006, 2013, 4031], true) || $this->pdo?->inTransaction()) {
                throw $e;
            }
            $this->pdo = null;
            $statement = $this->pdo()->prepare($sql);
            $statement->execute($bindings);
            return $statement;
        }
    }

    /** @return list<array<string, mixed>> */
    public function select(string $sql, array $bindings = []): array
    {
        return $this->query($sql, $bindings)->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public function first(string $sql, array $bindings = []): ?array
    {
        $row = $this->query($sql, $bindings)->fetch();
        return $row === false ? null : $row;
    }

    public function value(string $sql, array $bindings = []): mixed
    {
        $value = $this->query($sql, $bindings)->fetchColumn();
        return $value === false ? null : $value;
    }

    /** @return list<mixed> პირველი სვეტის მნიშვნელობები */
    public function column(string $sql, array $bindings = []): array
    {
        return $this->query($sql, $bindings)->fetchAll(PDO::FETCH_COLUMN);
    }

    /** DDL და სხვა ბრძანებები პარამეტრების გარეშე */
    public function statement(string $sql): void
    {
        $this->pdo()->exec($sql);
    }

    public function insert(string $table, array $data): int
    {
        $columns = implode(', ', array_map([self::class, 'ident'], array_keys($data)));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $this->query("INSERT INTO " . self::ident($table) . " ($columns) VALUES ($placeholders)", array_values($data));
        return (int)$this->pdo()->lastInsertId();
    }

    /** @param string $where მაგ. "id = ?" — მნიშვნელობები მხოლოდ $bindings-ით */
    public function update(string $table, array $data, string $where, array $bindings = []): int
    {
        $set = implode(', ', array_map(fn (string $column) => self::ident($column) . ' = ?', array_keys($data)));
        $sql = "UPDATE " . self::ident($table) . " SET $set WHERE $where";
        return $this->query($sql, [...array_values($data), ...$bindings])->rowCount();
    }

    public function delete(string $table, string $where, array $bindings = []): int
    {
        return $this->query("DELETE FROM " . self::ident($table) . " WHERE $where", $bindings)->rowCount();
    }

    /**
     * @template T
     * @param callable(DB): T $callback
     * @return T
     */
    public function transaction(callable $callback): mixed
    {
        $pdo = $this->pdo();
        $pdo->beginTransaction();
        try {
            $result = $callback($this);
            $pdo->commit();
            return $result;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /** ცხრილის/სვეტის სახელი ბრჭყალებში; მხოლოდ ასოები, ციფრები, "_" და "table.column" */
    public static function ident(string $name): string
    {
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*(\.[A-Za-z_][A-Za-z0-9_]*)?$/', $name)) {
            throw new InvalidArgumentException("Invalid identifier [$name].");
        }
        return '`' . str_replace('.', '`.`', $name) . '`';
    }
}
