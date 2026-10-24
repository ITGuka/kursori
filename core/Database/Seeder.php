<?php
declare(strict_types=1);

namespace Core\Database;

/**
 * database/seeds/*.php ფაილი აბრუნებს ამ კლასის მემკვიდრეს.
 * Seed-ები განმეორებით გაშვებადი უნდა იყოს (INSERT ... ON DUPLICATE KEY UPDATE).
 */
abstract class Seeder
{
    abstract public function run(DB $db): void;
}
