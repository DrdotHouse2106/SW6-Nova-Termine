<?php declare(strict_types=1);

namespace Nova\Termine\Subscriber;

use Doctrine\DBAL\Connection;
use Nova\Termine\Core\Content\Termin\TerminDefinition;
use Nova\Termine\Service\DateRange;
use Nova\Termine\Service\LinkValidator;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Command\UpdateCommand;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Command\WriteCommand;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Validation\PreWriteValidationEvent;
use Shopware\Core\Framework\Validation\WriteConstraintViolationException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * Serverseitige Prüfung beim Speichern (Administration, API, Formular): Link nur http/https,
 * Teilnahme und Status nur bekannte Werte, Ende nicht vor Beginn – auch bei Teil-Updates, die
 * nur Beginn oder nur Ende ändern (das Gegenstück wird dann aus der Datenbank gelesen).
 */
class TerminValidationSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly ?Connection $connection = null)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [PreWriteValidationEvent::class => 'validate'];
    }

    public function validate(PreWriteValidationEvent $event): void
    {
        foreach ($event->getCommandsForEntity(TerminDefinition::ENTITY_NAME) as $command) {
            $payload = $command->getPayload();
            $violations = new ConstraintViolationList();

            $url = $payload['url'] ?? null;
            if (\is_string($url) && $url !== '' && !LinkValidator::isValid($url)) {
                $violations->add($this->violation('Der Link muss mit http:// oder https:// beginnen.', '/url', $url));
            }

            $participation = $payload['participation'] ?? null;
            if ($participation !== null && !\in_array($participation, TerminDefinition::PARTICIPATIONS, true)) {
                $violations->add($this->violation('Unbekannte Teilnahme.', '/participation', $participation));
            }

            $status = $payload['status'] ?? null;
            if ($status !== null && !\in_array($status, TerminDefinition::STATUSES, true)) {
                $violations->add($this->violation('Unbekannter Status.', '/status', $status));
            }

            [$start, $end] = $this->dateRange($command, $payload);
            if ($start !== null && $end !== null && $end < $start) {
                $violations->add($this->violation('Das Ende liegt vor dem Beginn.', '/endDate', $payload['end_date'] ?? $end));
            }

            if ($violations->count() > 0) {
                $event->getExceptions()->add(new WriteConstraintViolationException($violations, $command->getPath()));
            }
        }
    }

    /**
     * Beginn und Ende als „Y-m-d“ (lexikografisch vergleichbar). Fehlt eines davon im Payload
     * eines Updates, gilt der gespeicherte Wert.
     *
     * @param array<string, mixed> $payload
     *
     * @return array{0: ?string, 1: ?string}
     */
    private function dateRange(WriteCommand $command, array $payload): array
    {
        $hasStart = \array_key_exists('start_date', $payload);
        $hasEnd = \array_key_exists('end_date', $payload);
        $start = DateRange::dayOf($payload['start_date'] ?? null);
        $end = DateRange::dayOf($payload['end_date'] ?? null);

        if ($hasStart === $hasEnd || !$command instanceof UpdateCommand || $this->connection === null) {
            return [$start, $end];
        }

        $id = $command->getPrimaryKey()['id'] ?? null;
        if (!\is_string($id)) {
            return [$start, $end];
        }

        $stored = $this->connection->fetchAssociative(
            'SELECT start_date, end_date FROM nova_termin WHERE id = :id',
            ['id' => $id],
        );
        if ($stored === false) {
            return [$start, $end];
        }

        return [
            $hasStart ? $start : DateRange::dayOf($stored['start_date'] ?? null),
            $hasEnd ? $end : DateRange::dayOf($stored['end_date'] ?? null),
        ];
    }

    private function violation(string $message, string $path, mixed $value): ConstraintViolation
    {
        return new ConstraintViolation($message, $message, [], null, $path, $value);
    }
}
