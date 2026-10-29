<?php declare(strict_types=1);

namespace Nova\Termine;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;

/**
 * Nova Termine – Veranstaltungstermine (Treffen, Teilemärkte, Messen) in der Administration pflegen
 * und über das Erlebniswelten-Element „Terminliste“ im Shop anzeigen.
 */
class NovaTermine extends Plugin
{
    public function uninstall(UninstallContext $uninstallContext): void
    {
        parent::uninstall($uninstallContext);

        if ($uninstallContext->keepUserData()) {
            return;
        }

        $connection = $this->container?->get(Connection::class);
        if (!$connection instanceof Connection) {
            return;
        }

        // Reihenfolge wegen Fremdschlüsseln: erst Zuordnungen und Übersetzungen, dann die Termine
        $connection->executeStatement('DROP TABLE IF EXISTS `nova_termin_sales_channel`');
        $connection->executeStatement('DROP TABLE IF EXISTS `nova_termin_translation`');
        $connection->executeStatement('DROP TABLE IF EXISTS `nova_termin`');
        $connection->executeStatement('DELETE FROM `system_config` WHERE `configuration_key` LIKE \'NovaTermine.%\'');
    }
}
