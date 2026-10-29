<?php declare(strict_types=1);

namespace Nova\Termine\Controller;

use Nova\Termine\Service\PickupService;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Storefront\Framework\Routing\StorefrontRouteScope;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Merkt sich das gewählte Abhol-Treffen in der Sitzung (Auswahl auf der Bestätigungsseite sendet sich
 * beim Ändern selbst ab), damit es nach dem Neuladen – z. B. nach einem Wechsel der Zahlungsart –
 * erhalten bleibt.
 */
#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [StorefrontRouteScope::ID]])]
class PickupController extends StorefrontController
{
    public function __construct(private readonly PickupService $pickup)
    {
    }

    #[Route(
        path: '/nova-termine/pickup',
        name: 'frontend.nova-termine.pickup',
        methods: [Request::METHOD_POST]
    )]
    public function select(Request $request, SalesChannelContext $context): RedirectResponse
    {
        $id = $request->request->get(PickupService::FIELD);
        $key = PickupService::SESSION_KEY . '.' . $context->getSalesChannelId();

        if ($request->hasSession()) {
            $termin = \is_string($id) ? $this->pickup->find($context, $id) : null;
            if ($termin !== null) {
                $request->getSession()->set($key, $termin->getId());
            } else {
                $request->getSession()->remove($key);
            }
        }

        return $this->redirectToRoute('frontend.checkout.confirm.page');
    }
}
