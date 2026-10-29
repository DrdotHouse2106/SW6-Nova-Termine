<?php declare(strict_types=1);

namespace Nova\Termine\ScheduledTask;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

/**
 * Stündlich: Hat seit dem letzten Lauf ein neuer Tag begonnen (Zeitzone des Shops), werden alle
 * Seiten mit Terminliste neu aufgebaut – abgelaufene Termine verschwinden so kurz nach Mitternacht.
 */
class RefreshListTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'nova_termine.refresh_list';
    }

    public static function getDefaultInterval(): int
    {
        return self::HOURLY;
    }

    public static function shouldRescheduleOnFailure(): bool
    {
        return true;
    }
}
