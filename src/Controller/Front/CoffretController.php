<?php

namespace App\Controller\Front;

use App\Repository\CoffretRepository;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CoffretController extends AbstractController
{
    #[Route('/coffrets', name: 'app_front_coffret_index')]
    public function index(CoffretRepository $coffretRepository): Response
    {
        $coffrets = $coffretRepository->findBy(
            ['isActive' => true],
            ['createdAt' => 'DESC']
        );

        return $this->render('front/coffret/index.html.twig', [
            'coffrets' => $coffrets,
        ]);
    }

    #[Route('/coffrets/{id}/ajouter', name: 'app_front_coffret_add', methods: ['POST'])]
    public function add(int $id, CoffretRepository $coffretRepository, CartService $cartService): Response
    {
        $coffret = $coffretRepository->find($id);

        if (!$coffret || !$coffret->isActive()) {
            throw $this->createNotFoundException('Coffret introuvable.');
        }

        $cartService->addCoffret($id);
        $this->addFlash('success', 'Le coffret "' . $coffret->getNom() . '" a été ajouté au panier !');

        return $this->redirectToRoute('app_front_coffret_index');
    }

    #[Route('/coffrets/{id}/panier/modifier', name: 'app_front_coffret_cart_update', methods: ['POST'])]
    public function update(int $id, Request $request, CartService $cartService): Response
    {
        $quantity = (int) $request->request->get('quantity', 1);
        $cartService->updateCoffretQuantity($id, $quantity);
        return $this->redirectToRoute('app_front_cart_index');
    }

    #[Route('/coffrets/{id}/panier/supprimer', name: 'app_front_coffret_cart_remove', methods: ['POST'])]
    public function removeFromCart(int $id, CartService $cartService): Response
    {
        $cartService->removeCoffret($id);
        $this->addFlash('success', 'Coffret retiré du panier.');
        return $this->redirectToRoute('app_front_cart_index');
    }
}