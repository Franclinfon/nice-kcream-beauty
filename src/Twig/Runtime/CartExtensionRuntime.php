<?php

namespace App\Twig\Runtime;

use App\Service\CartService;
use Twig\Extension\RuntimeExtensionInterface;

class CartExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private CartService $cartService,
    ) {
    }

    public function getCartItemCount(): int
    {
        return $this->cartService->getItemCount();
    }
}