<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;

class CheckoutService
{
    private const SESSION_ADDRESS_KEY = 'checkout_address_id';
    private const SESSION_DELIVERY_KEY = 'checkout_delivery_method';
    private const SESSION_PENDING_ORDER_KEY = 'checkout_pending_order_id';

    public function __construct(
        private RequestStack $requestStack,
    ) {
    }

    private function getSession()
    {
        return $this->requestStack->getSession();
    }

    public function setAddressId(int $addressId): void
    {
        $this->getSession()->set(self::SESSION_ADDRESS_KEY, $addressId);
    }

    public function getAddressId(): ?int
    {
        return $this->getSession()->get(self::SESSION_ADDRESS_KEY);
    }

    public function setDeliveryMethod(string $method): void
    {
        $this->getSession()->set(self::SESSION_DELIVERY_KEY, $method);
    }

    public function getDeliveryMethod(): ?string
    {
        return $this->getSession()->get(self::SESSION_DELIVERY_KEY);
    }

    public function setPendingOrderId(int $orderId): void
    {
        $this->getSession()->set(self::SESSION_PENDING_ORDER_KEY, $orderId);
    }

    public function getPendingOrderId(): ?int
    {
        return $this->getSession()->get(self::SESSION_PENDING_ORDER_KEY);
    }

    public function clear(): void
    {
        $session = $this->getSession();
        $session->remove(self::SESSION_ADDRESS_KEY);
        $session->remove(self::SESSION_DELIVERY_KEY);
        $session->remove(self::SESSION_PENDING_ORDER_KEY);
    }

    public function calculateShippingCost(string $deliveryMethod, float $cartTotal): float
    {
        if ($deliveryMethod === 'retrait_boutique') {
            return 0.0;
        }

        // colissimo
        return $cartTotal >= 70.0 ? 0.0 : 5.90;
    }
}