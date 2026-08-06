<?php

namespace App\Service;

use App\Entity\Coffret;
use App\Entity\Product;
use App\Repository\CoffretRepository;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{
    private const SESSION_KEY = 'cart';
    private const SESSION_KEY_COFFRETS = 'cart_coffrets';

    public function __construct(
        private RequestStack $requestStack,
        private ProductRepository $productRepository,
        private CoffretRepository $coffretRepository,
    ) {
    }

    private function getSession()
    {
        return $this->requestStack->getSession();
    }

    // ===== PRODUITS =====

    public function getCart(): array
    {
        return $this->getSession()->get(self::SESSION_KEY, []);
    }

    public function add(int $productId, int $quantity = 1): void
    {
        $cart = $this->getCart();
        $cart[$productId] = ($cart[$productId] ?? 0) + $quantity;
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

    // ===== COFFRETS =====

    public function getCoffretCart(): array
    {
        return $this->getSession()->get(self::SESSION_KEY_COFFRETS, []);
    }

    public function addCoffret(int $coffretId, int $quantity = 1): void
    {
        $cart = $this->getCoffretCart();
        $cart[$coffretId] = ($cart[$coffretId] ?? 0) + $quantity;
        $this->getSession()->set(self::SESSION_KEY_COFFRETS, $cart);
    }

    public function updateCoffretQuantity(int $coffretId, int $quantity): void
    {
        $cart = $this->getCoffretCart();
        if ($quantity <= 0) {
            unset($cart[$coffretId]);
        } else {
            $cart[$coffretId] = $quantity;
        }
        $this->getSession()->set(self::SESSION_KEY_COFFRETS, $cart);
    }

    public function removeCoffret(int $coffretId): void
    {
        $cart = $this->getCoffretCart();
        unset($cart[$coffretId]);
        $this->getSession()->set(self::SESSION_KEY_COFFRETS, $cart);
    }

    public function clear(): void
    {
        $this->getSession()->remove(self::SESSION_KEY);
        $this->getSession()->remove(self::SESSION_KEY_COFFRETS);
    }

    // ===== ITEMS COMBINÉS =====

    public function getCartItems(): array
    {
        $items = [];

        // Produits
        foreach ($this->getCart() as $productId => $quantity) {
            $product = $this->productRepository->find($productId);
            if (!$product || !$product->isActive()) continue;

            $unitPrice = $product->getPrixPromo() ?? $product->getPrix();
            $items[] = [
                'type'     => 'product',
                'product'  => $product,
                'coffret'  => null,
                'quantity' => $quantity,
                'subtotal' => (float) $unitPrice * $quantity,
            ];
        }

        // Coffrets
        foreach ($this->getCoffretCart() as $coffretId => $quantity) {
            $coffret = $this->coffretRepository->find($coffretId);
            if (!$coffret || !$coffret->isActive()) continue;

            $items[] = [
                'type'     => 'coffret',
                'product'  => null,
                'coffret'  => $coffret,
                'quantity' => $quantity,
                'subtotal' => (float) $coffret->getPrix() * $quantity,
            ];
        }

        return $items;
    }

    public function getTotal(): float
    {
        return array_sum(array_column($this->getCartItems(), 'subtotal'));
    }

    public function getItemCount(): int
    {
        return array_sum($this->getCart()) + array_sum($this->getCoffretCart());
    }
}