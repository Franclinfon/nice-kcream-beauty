<?php

namespace App\Service;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{
    private const SESSION_KEY = 'cart';

    public function __construct(
        private RequestStack $requestStack,
        private ProductRepository $productRepository,
    ) {
    }

    private function getSession()
    {
        return $this->requestStack->getSession();
    }

    public function getCart(): array
    {
        return $this->getSession()->get(self::SESSION_KEY, []);
    }

    public function add(int $productId, int $quantity = 1): void
    {
        $cart = $this->getCart();

        if (isset($cart[$productId])) {
            $cart[$productId] += $quantity;
        } else {
            $cart[$productId] = $quantity;
        }

        $this->getSession()->set(self::SESSION_KEY, $cart);
    }

    public function updateQuantity(int $productId, int $quantity): void
    {
        $cart = $this->getCart();

        if ($quantity <= 0) {
            unset($cart[$productId]);
        } else {
            $cart[$productId] = $quantity;
        }

        $this->getSession()->set(self::SESSION_KEY, $cart);
    }

    public function remove(int $productId): void
    {
        $cart = $this->getCart();
        unset($cart[$productId]);
        $this->getSession()->set(self::SESSION_KEY, $cart);
    }

    public function clear(): void
    {
        $this->getSession()->remove(self::SESSION_KEY);
    }

    /**
     * Retourne les lignes du panier avec les objets Product résolus et les sous-totaux.
     *
     * @return array<int, array{product: Product, quantity: int, subtotal: float}>
     */
    public function getCartItems(): array
    {
        $cart = $this->getCart();
        $items = [];

        foreach ($cart as $productId => $quantity) {
            $product = $this->productRepository->find($productId);

            if (!$product || !$product->isActive()) {
                continue;
            }

            $unitPrice = $product->getPrixPromo() ?? $product->getPrix();

            $items[] = [
                'product' => $product,
                'quantity' => $quantity,
                'subtotal' => (float) $unitPrice * $quantity,
            ];
        }

        return $items;
    }

    public function getTotal(): float
    {
        $total = 0.0;

        foreach ($this->getCartItems() as $item) {
            $total += $item['subtotal'];
        }

        return $total;
    }

    public function getItemCount(): int
    {
        return array_sum($this->getCart());
    }
}