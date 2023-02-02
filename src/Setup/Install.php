<?php

/**
 * shopdriven
 *
 * @category  shopdriven
 * @package   Shopware\Plugins\DrivenProductConfigurator
 * @copyright (c) 2022 shopdriven
 */

namespace Driven\ProductConfigurator\Setup;

use Doctrine\DBAL\Connection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Uuid\Uuid;

class Install
{
    /**
     * Main bootstrap object.
     *
     * @var Plugin
     */
    private $plugin;

    /**
     * ...
     *
     * @var InstallContext
     */
    private $context;

    /**
     * ...
     *
     * @var Connection
     */
    private $connection;

    /**
     * ...
     *
     * @var EntityRepositoryInterface
     */
    private $customFieldSetRepository;

    /**
     * ...
     *
     * @var EntityRepositoryInterface
     */
    private $customFieldRepository;

    /**
     * ...
     *
     * @param Plugin $plugin
     * @param InstallContext $context
     * @param Connection $connection
     * @param EntityRepositoryInterface $customFieldSetRepository
     * @param EntityRepositoryInterface $customFieldRepository
     */
    public function __construct(Plugin $plugin, InstallContext $context, Connection $connection, EntityRepositoryInterface $customFieldSetRepository, EntityRepositoryInterface $customFieldRepository)
    {
        // set params
        $this->plugin = $plugin;
        $this->context = $context;
        $this->connection = $connection;
        $this->customFieldSetRepository = $customFieldSetRepository;
        $this->customFieldRepository = $customFieldRepository;
    }

    /**
     * ...
     */
    public function install(): void
    {
        // install everything
        $this->installCustomFields();

        // create custom entity
        $query = '
        CREATE TABLE IF NOT EXISTS `driven_product_configurator` (
            `id` BINARY(16) NOT NULL,
            `forehead` BINARY(16) NULL DEFAULT NULL,
            `backhead` BINARY(16) NULL DEFAULT NULL,
            `sealing` VARCHAR(255) NULL DEFAULT NULL,
            `customer_id` BINARY(16) NULL DEFAULT NULL,
            `product_id` BINARY(16) NULL DEFAULT NULL,
            `product_version_id` BINARY(16) NULL DEFAULT NULL,
            `sales_channel_id` BINARY(16) NULL DEFAULT NULL,
            CONSTRAINT `fk.driven_product_configurator.customer_id` FOREIGN KEY (`customer_id`)
                REFERENCES `customer` (`id`) ON DELETE SET NULL,
            CONSTRAINT `fk.driven_product_configurator.pid__pvid` FOREIGN KEY (`product_id`, `product_version_id`)
                    REFERENCES `product` (`id`, `version_id`) ON DELETE SET NULL,
            CONSTRAINT `fk.driven_product_configurator.sales_channel_id` FOREIGN KEY (`sales_channel_id`)
                REFERENCES `sales_channel` (`id`) ON DELETE SET NULL,
            `created_at` DATETIME(3) NOT NULL,
            `updated_at` DATETIME(3),
            PRIMARY KEY (`id`)
        )
        ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
    ';
        $this->connection->executeStatement($query);
    }

    /**
     * ...
     */
    private function installCustomFields(): void
    {
        // create custom fields
        foreach (DataHolder\CustomFields::$customFields as $customField) {
            // and save it and ignore exceptions
            try {
                $this->customFieldSetRepository->upsert(
                    [$customField],
                    $this->context->getContext()
                );
            } catch (\Exception $exception) {
            }
        }
    }
}
