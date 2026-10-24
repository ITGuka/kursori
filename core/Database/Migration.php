<?php
declare(strict_types=1);

namespace Core\Database;

/**
 * database/migrations/*.php ფაილი აბრუნებს ამ კლასის ანონიმურ მემკვიდრეს:
 * return new class extends Migration { public function up(DB $db): void { ... } ... };
 */
abstract class Migration
{
    abstract public function up(DB $db): void;

    abstract public function down(DB $db): void;

    /** სტანდარტული ცხრილის სუფიქსი: InnoDB + utf8mb4 */
    protected const TABLE_OPTIONS = 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

    /** created_at / updated_at სვეტები */
    protected const TIMESTAMPS = 'created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP';
}
