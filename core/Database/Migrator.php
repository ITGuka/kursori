<?php
declare(strict_types=1);

namespace Core\Database;

use RuntimeException;

/**
 * მიგრაციების გაშვება batch-ებად; ისტორია ინახება `migrations` ცხრილში.
 * MySQL-ში DDL ავტომატურად აკომიტებს, ამიტომ ტრანზაქციები აქ არ გამოიყენება.
 */
final class Migrator
{
    public function __construct(private readonly DB $db, private readonly string $path)
    {
    }

    /** @return list<string> გაშვებული მიგრაციები */
    public function migrate(?callable $onRun = null): array
    {
        $this->ensureTable();
        $pending = array_diff(array_keys($this->files()), $this->ran());
        if ($pending === []) {
            return [];
        }

        $batch = (int)$this->db->value('SELECT COALESCE(MAX(batch), 0) FROM migrations') + 1;
        foreach ($pending as $name) {
            $this->load($name)->up($this->db);
            $this->db->insert('migrations', ['migration' => $name, 'batch' => $batch]);
            $onRun && $onRun($name);
        }
        return array_values($pending);
    }

    /** @return list<string> დაბრუნებული მიგრაციები (ბოლო $steps batch) */
    public function rollback(int $steps = 1, ?callable $onRun = null): array
    {
        $this->ensureTable();
        $rows = $this->db->select(
            'SELECT migration FROM migrations WHERE batch > (SELECT COALESCE(MAX(batch), 0) - ? FROM (SELECT batch FROM migrations) AS b) ORDER BY id DESC',
            [max(1, $steps)],
        );

        $done = [];
        foreach ($rows as $row) {
            $this->load($row['migration'])->down($this->db);
            $this->db->delete('migrations', 'migration = ?', [$row['migration']]);
            $onRun && $onRun($row['migration']);
            $done[] = $row['migration'];
        }
        return $done;
    }

    /** @return array<string, int|null> მიგრაცია => batch (null — ჯერ არ გაშვებულა) */
    public function status(): array
    {
        $this->ensureTable();
        $ran = [];
        foreach ($this->db->select('SELECT migration, batch FROM migrations') as $row) {
            $ran[$row['migration']] = (int)$row['batch'];
        }

        $status = [];
        foreach (array_keys($this->files()) as $name) {
            $status[$name] = $ran[$name] ?? null;
        }
        return $status;
    }

    private function ensureTable(): void
    {
        $this->db->statement('CREATE TABLE IF NOT EXISTS migrations (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL UNIQUE,
            batch INT UNSIGNED NOT NULL,
            ran_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    }

    /** @return list<string> */
    private function ran(): array
    {
        return $this->db->column('SELECT migration FROM migrations ORDER BY id');
    }

    /** @return array<string, string> სახელი => ფაილი, სახელის მიხედვით დალაგებული */
    private function files(): array
    {
        $files = [];
        foreach (glob($this->path . DIRECTORY_SEPARATOR . '*.php') ?: [] as $file) {
            $files[basename($file, '.php')] = $file;
        }
        ksort($files);
        return $files;
    }

    private function load(string $name): Migration
    {
        $file = $this->files()[$name] ?? throw new RuntimeException("Migration file [$name] not found.");
        $migration = require $file;
        if (!$migration instanceof Migration) {
            throw new RuntimeException("[$name] must return an instance of " . Migration::class . '.');
        }
        return $migration;
    }
}
