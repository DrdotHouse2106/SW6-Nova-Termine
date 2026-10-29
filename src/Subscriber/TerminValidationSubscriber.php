<?php declare(strict_types=1);

namespace Nova\Termine\Subscriber;

use Nova\Termine\Core\Content\Termin\TerminDefinition;
use Nova\Termine\Service\LinkValidator;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Validation\PreWriteValidationEvent;
use Shopware\Core\Framework\Validation\WriteConstraintViolationException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * Serverseitige Prüfung beim Speichern (Administration, API, Formular): Link nur http/https,
 * Teilnahme und Status nur bekannte Werte, Ende nicht vor Beginn.
 */
class TerminValidationSubscriber implements EventSubscriberInterface
{
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

            $start = $payload['start_date'] ?? null;
            $end = $payload['end_date'] ?? null;
            if (\is_string($start) && \is_string($end) && substr($end, 0, 10) < substr($start, 0, 10)) {
                $violations->add($this->violation('Das Ende liegt vor dem Beginn.', '/endDate', $end));
            }

            if ($violations->count() > 0) {
                $event->getExceptions()->add(new WriteConstraintViolationException($violations, $command->getPath()));
            }
        }
    }

    private function violation(string $message, string $path, mixed $value): ConstraintViolation
    {
        return new ConstraintViolation($message, $message, [], null, $path, $value);
    }
}
