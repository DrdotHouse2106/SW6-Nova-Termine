<?php declare(strict_types=1);

namespace Nova\Termine\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * Tabellen für Termine, ihre Übersetzungen (Titel, Programm) und die Verkaufskanal-Zuordnung.
 */
class Migration1793250000NovaTermin extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1793250000;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement(<<<'SQL'
            CREATE TABLE IF NOT EXISTS `nova_termin` (
                `id` BINARY(16) NOT NULL,
                `location` VARCHAR(255) NOT NULL,
                `country` VARCHAR(100) NULL,
                `start_date` DATE NOT NULL,
                `end_date` DATE NULL,
                `url` VARCHAR(500) NULL,
                `participation` VARCHAR(20) NOT NULL DEFAULT 'open',
                `status` VARCHAR(20) NOT NULL DEFAULT 'active',
                `media_id` BINARY(16) NULL,
                `internal_note` LONGTEXT NULL,
                `reporter_email` VARCHAR(255) NULL,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
                PRIMARY KEY (`id`),
                KEY `idx.nova_termin.status_dates` (`status`, `end_date`, `start_date`),
                CONSTRAINT `fk.nova_termin.media_id` FOREIGN KEY (`media_id`)
                    REFERENCES `media` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            SQL);

        $connection->executeStatement(<<<'SQL'
            CREATE TABLE IF NOT EXISTS `nova_termin_translation` (
                `nova_termin_id` BINARY(16) NOT NULL,
                `language_id` BINARY(16) NOT NULL,
                `title` VARCHAR(255) NULL,
                `program` VARCHAR(1000) NULL,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
                PRIMARY KEY (`nova_termin_id`, `language_id`),
                CONSTRAINT `fk.nova_termin_translation.nova_termin_id` FOREIGN KEY (`nova_termin_id`)
                    REFERENCES `nova_termin` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.nova_termin_translation.language_id` FOREIGN KEY (`language_id`)
                    REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            SQL);

        $connection->executeStatement(<<<'SQL'
            CREATE TABLE IF NOT EXISTS `nova_termin_sales_channel` (
                `nova_termin_id` BINARY(16) NOT NULL,
                `sales_channel_id` BINARY(16) NOT NULL,
                PRIMARY KEY (`nova_termin_id`, `sales_channel_id`),
                CONSTRAINT `fk.nova_termin_sales_channel.nova_termin_id` FOREIGN KEY (`nova_termin_id`)
                    REFERENCES `nova_termin` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.nova_termin_sales_channel.sales_channel_id` FOREIGN KEY (`sales_channel_id`)
                    REFERENCES `sales_channel` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            SQL);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
