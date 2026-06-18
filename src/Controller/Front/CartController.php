<?php

namespace App\Controller\Front;

use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CartController extends AbstractController
{
    #[Route('/panier', name: 'app_front_cart_index')]
    public function index(CartService $cartService): Response
    {
        return $this->render('front/cart/index.html.twig', [
            'cartItems' => $cartService->getCartItems(),
            'total' => $cartService->getTotal(),
        ]);
    }

    #[Route('/panier/ajouter/{id}', name: 'app_front_cart_add', methods: ['POST'])]
    public function add(int $id, Request $request, CartService $cartService): Response
    {
        $quantity = max(1, (int) $request->request->get('quantity', 1));
        $cartService->add($id, $quantity);

        $this->addFlash('success', 'Produit ajouté au panier.');

        return $this->redirectToRoute('app_front_cart_index');
    }

    #[Route('/panier/modifier/{id}', name: 'app_front_cart_update', methods: ['POST'])]
    public function update(int $id, Request $request, CartService $cartService): Response
    {
        $quantity = (int) $request->request->get('quantity', 1);
        $cartService->updateQuantity($id, $quantity);

        return $this->redirectToRoute('app_front_cart_index');
    }

    #[Route('/panier/supprimer/{id}', name: 'app_front_cart_remove', methods: ['POST'])]
    public function remove(int $id, CartService $cartService): Response
    {
        $cartService->remove($id);

        $this->addFlash('success', 'Produit retiré du panier.');

        return $this->redirectToRoute('app_front_cart_index');
    }
}