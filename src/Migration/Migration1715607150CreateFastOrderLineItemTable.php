<?php declare(strict_types=1);

namespace FastOrder\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1715607150CreateFastOrderLineItemTable extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1715607150;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `fast_order_line_item` (
    `id` BINARY(16) NOT NULL,
    `session_id` VARCHAR(255) NOT NULL,
    `product_number` VARCHAR(64) NOT NULL,
    `quantity` INT NOT NULL,
    `comment` VARCHAR(255) DEFAULT NULL,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3),
    PRIMARY KEY (`id`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;
SQL;

        $connection->executeStatement($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
