<?php declare(strict_types=1);

namespace Nova\Termine\Checkout;

use Nova\Termine\Service\PickupService;
use Shopware\Core\Checkout\Shipping\SalesChannel\AbstractShippingMethodRoute;
use Shopware\Core\Checkout\Shipping\SalesChannel\ShippingMethodRouteResponse;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

/**
 * Blendet die Abhol-Versandart aus, solange kein passender Termin ansteht. Gilt für Storefront
 * (Warenkorb, Kasse) und Store-API, weil beide Shopwares Versandarten-Route verwenden.
 */
class PickupShippingMethodRoute extends AbstractShippingMethodRoute
{
    public function __construct(
        private readonly AbstractShippingMethodRoute $decorated,
        private readonly PickupService $pickup,
    ) {
    }

    public function getDecorated(): AbstractShippingMethodRoute
    {
        return $this->decorated;
    }

    public function load(Request $request, SalesChannelContext $context, Criteria $criteria): ShippingMethodRouteResponse
    {
        $response = $this->decorated->load($request, $context, $criteria);

        $pickupId = $this->pickup->getShippingMethodId($context->getSalesChannelId());
        if ($pickupId === null) {
            return $response;
        }

        $methods = $response->getShippingMethods();
        foreach ($methods->getIds() as $id) {
            if (strtolower((string) $id) === strtolower($pickupId) && $this->pickup->getAvailable($context) === []) {
                $methods->remove((string) $id);
            }
        }

        return $response;
    }
}
