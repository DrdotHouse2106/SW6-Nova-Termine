<?php declare(strict_types=1);

namespace Nova\Termine\ScheduledTask;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

/**
 * Täglich: Vorschläge aus dem Formular „Treffen melden“, die 180 Tage nicht übernommen wurden, löschen.
 */
class CleanupSuggestionsTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'nova_termine.cleanup_suggestions';
    }

    public static function getDefaultInterval(): int
    {
        return self::DAILY;
    }
}
