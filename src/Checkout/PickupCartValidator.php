<?php declare(strict_types=1);

namespace Nova\Termine\Checkout;

use Nova\Termine\Service\PickupService;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartValidatorInterface;
use Shopware\Core\Checkout\Cart\Error\ErrorCollection;
use Shopware\Core\Checkout\Shipping\Cart\Error\ShippingMethodBlockedError;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * Ist die Abhol-Versandart gewählt, aber kein Termin mehr verfügbar (z. B. Frist abgelaufen), wird sie
 * wie eine gesperrte Versandart behandelt: Shopware wechselt in der Kasse automatisch auf eine andere
 * Versandart und zeigt den üblichen Hinweis; bestellen lässt sich so nicht.
 */
class PickupCartValidator implements CartValidatorInterface
{
    public function __construct(private readonly PickupService $pickup)
    {
    }

    public function validate(Cart $cart, ErrorCollection $errors, SalesChannelContext $context): void
    {
        if (!$this->pickup->isActive($context) || $this->pickup->getAvailable($context) !== []) {
            return;
        }

        $method = $context->getShippingMethod();
        $errors->add(new ShippingMethodBlockedError(
            (string) ($method->getTranslation('name') ?? $method->getName() ?? ''),
            $method->getId(),
            'Kein Treffen zur Abholung verfügbar'
        ));
    }
}
