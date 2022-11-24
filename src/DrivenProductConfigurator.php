<?php declare(strict_types=1);

namespace Driven\ProductConfigurator;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DrivenProductConfigurator extends Plugin
{

    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container): void
    {
        // set plugin parameters
        $container->setParameter('dvsn.set_configurator.path', $this->getPath());

        // call parent
        parent::build($container);
    }

    /**
     * {@inheritDoc}
     */
    public function activate(ActivateContext $activateContext): void
    {
    }

    /**
     * {@inheritDoc}
     */
    public function install(InstallContext $installContext): void
    {
        // call installer
        $installer = new Setup\Install(
            $this,
            $installContext,
            $this->container->get(Connection::class),
            $this->container->get('custom_field_set.repository'),
            $this->container->get('custom_field.repository'),
        );
        $installer->install();

        // call updater
        $installer = new Setup\Update(
            $this,
            $installContext,
            $this->container->get(Connection::class),
            $this->container->get('custom_field_set.repository'),
            $this->container->get('custom_field.repository')
        );
        $installer->install();
    }

    /**
     * {@inheritDoc}
     */
    public function postInstall(InstallContext $installContext): void
    {
    }

    /**
     * {@inheritDoc}
     */
    public function update(UpdateContext $updateContext): void
    {
        // call updater
        $installer = new Setup\Update(
            $this,
            $updateContext,
            $this->container->get(Connection::class),
            $this->container->get('custom_field_set.repository'),
            $this->container->get('custom_field.repository')
        );
        $installer->update($updateContext->getCurrentPluginVersion());
    }

    /**
     * {@inheritDoc}
     */
    public function postUpdate(UpdateContext $updateContext): void
    {
    }

    /**
     * {@inheritDoc}
     */
    public function uninstall(UninstallContext $context): void
    {
        // call uninstaller
        $installer = new Setup\Uninstall(
            $this,
            $context,
            $this->container->get(Connection::class),
            $this->container->get('custom_field_set.repository'),
            $this->container->get('custom_field.repository')
        );
        $installer->uninstall();
    }
}