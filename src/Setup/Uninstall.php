<?php

/**
 * shopdriven
 *
 * @category  shopdriven
 * @package   Shopware\Plugins\DrivenProducttConfigurator
 * @copyright (c) 2022 shopdriven
 */

namespace Driven\ProductConfigurator\Setup;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\CustomField\Aggregate\CustomFieldSet\CustomFieldSetEntity;

class Uninstall
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
     * @var UninstallContext
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
     * @param Plugin                    $plugin
     * @param UninstallContext          $context
     * @param Connection                $connection
     * @param EntityRepositoryInterface $customFieldSetRepository
     * @param EntityRepositoryInterface $customFieldRepository
     */
    public function __construct(Plugin $plugin, UninstallContext $context, Connection $connection, EntityRepositoryInterface $customFieldSetRepository, EntityRepositoryInterface $customFieldRepository)
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
    public function uninstall(): void
    {
        // keep user data?
        if ($this->context->keepUserData()) {
            // dont remove anything
            return;
        }

        // clear plugin data
        $this->removeCustomFields();
    }

    /**
     * ...
     */
    private function removeCustomFields(): void
    {
        // remove the data itself (including translation table) from the custom_fields column
        foreach (DataHolder\CustomFields::$customFields as $customFieldSet) {
            foreach ($customFieldSet['customFields'] as $customField) {
                foreach ($customFieldSet['relations'] as $relation) {
                    $query = '
                        UPDATE `' . $relation['entityName'] . '`
                        SET `custom_fields` = JSON_REMOVE(`custom_fields`, "$.' . $customField['name'] . '");
                    ';
                    try {
                        $this->connection->executeStatement($query);
                    } catch (\Exception $exception) {}

                    $query = '
                        UPDATE `' . $relation['entityName'] . '_translation`
                        SET `custom_fields` = JSON_REMOVE(`custom_fields`, "$.' . $customField['name'] . '");
                    ';
                    try {
                        $this->connection->executeStatement($query);
                    } catch (\Exception $exception) {}
                }
            }
        }

        // remove every custom field
        foreach (DataHolder\CustomFields::$customFields as $customField) {
            /** @var CustomFieldSetEntity $customFieldSet */
            $customFieldSet = $this->customFieldSetRepository->search(
                (new Criteria())
                    ->addFilter(new EqualsFilter('custom_field_set.name', $customField['name'])),
                $this->context->getContext()
            )->first();

            // not found?
            if (!$customFieldSet instanceof CustomFieldSetEntity) {
                // ignore it
                continue;
            }

            // remove it
            $this->customFieldSetRepository->delete(
                [['id' => $customFieldSet->getId()]],
                $this->context->getContext()
            );
        }
    }
}
