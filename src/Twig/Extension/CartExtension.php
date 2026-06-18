<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\CartExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CartExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('cart_item_count', [CartExtensionRuntime::class, 'getCartItemCount']),
        ];
    }
}