<?php declare(strict_types=1);

namespace Driven\ProductConfigurator\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1672265472 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1672265472;
    }

    /**
     * @throws Exception
     */
    public function update(Connection $connection): void
    {
        // create custom entity
        $query = '
            CREATE TABLE IF NOT EXISTS `driven_product_configurator` (
                `id` BINARY(16) NOT NULL,
                `name` VARCHAR(255) NOT NULL,
                `position` VARCHAR(255) NOT NULL,
                `listing_price` VARCHAR(255) NOT NULL,
                `free` TINYINT(1) NOT NULL,
                `rebate` INT(11) NOT NULL,
                `forehead` BINARY(16) NULL DEFAULT NULL,
                `backhead` BINARY(16) NULL DEFAULT NULL,
                `sealing` BINARY(16) NULL DEFAULT NULL,
                `customer_id` BINARY(16) NULL DEFAULT NULL,
                `product_id` BINARY(16) NULL DEFAULT NULL,
                `product_version_id` BINARY(16) NULL DEFAULT NULL,
                `sales_channel_id` BINARY(16) NULL DEFAULT NULL,
                CONSTRAINT `fk.driven_configurator.customer_id` FOREIGN KEY (`customer_id`)
                    REFERENCES `customer` (`id`) ON DELETE SET NULL,
                CONSTRAINT `fk.driven_configurator.pid__pvid` FOREIGN KEY (`product_id`, `product_version_id`)
                    REFERENCES `product` (`id`, `version_id`) ON DELETE SET NULL,
                CONSTRAINT `fk.driven_configurator.sales_channel_id` FOREIGN KEY (`sales_channel_id`)
                    REFERENCES `sales_channel` (`id`) ON DELETE SET NULL
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3),
                PRIMARY KEY (`id`)
            )
            ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
        ';
        $connection->executeStatement($query);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
