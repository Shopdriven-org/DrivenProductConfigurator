<?php

/**
 * shopdriven
 *
 * @category  shopdriven
 * @package   Shopware\Plugins\DrivenProductConfigurator
 * @copyright (c) 2020 shopdriven
 */

namespace Driven\ProductConfigurator\Setup;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Uuid\Uuid;

class Update
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
        // call the update
        $this->update('0.0.0');
    }

    /**
     * ...
     *
     * @param string $preUpdateVersion
     */
    public function update($preUpdateVersion): void
    {
        // ignore on installation
        if ($preUpdateVersion === '0.0.0') {
            // stop
            return;
        }

        // always update custom fields
        $this->installCustomFields();
    }

    /**
     * ...
     */
    private function installCustomFields(): void
    {
        // create custom field sets with custom fields
        foreach ($this->getCustomFields() as $customField) {
            try {
                $this->customFieldSetRepository->upsert(
                    [$customField],
                    $this->context->getContext()
                );
            }
            catch (\Exception $exception) {}
        }

        // update every single custom field
        foreach ($this->getCustomFields() as $set) {
            foreach ($set['customFields'] as $customField) {
                $customField['customFieldSetId'] = $set['id'];

                $this->customFieldRepository->upsert(
                    [$customField],
                    $this->context->getContext()
                );
            }
        }
    }

    /**
     * Returns the custom fields from the data holder but inserts missing
     * id values which may be null.
     *
     * @return array
     */
    private function getCustomFields(): array
    {
        $customFields = DataHolder\CustomFields::$customFields;

        foreach ($customFields as $i => $group) {
            if ($group['id'] === null) {
                try {
                    $query = '
                        SELECT id
                        FROM custom_field_set
                        WHERE name = :name
                    ';
                    $id = $this->connection->fetchOne($query, ['name' => $group['name']]);

                    if ($id !== false) {
                        $customFields[$i]['id'] = Uuid::fromBytesToHex($id);
                    }
                } catch (\Exception $exception) {
                }
            }

            foreach ($group['customFields'] as $j => $customField) {
                if ($customField['id'] === null) {
                    try {
                        $query = '
                            SELECT id
                            FROM custom_field
                            WHERE name = :name
                        ';
                        $id = $this->connection->fetchOne($query, ['name' => $customField['name']]);

                        if ($id !== false) {
                            $customFields[$i]['customFields'][$j]['id'] = Uuid::fromBytesToHex($id);

                        }
                    } catch (\Exception $exception) {
                    }
                }
            }
        }

        return $customFields;
    }
}
