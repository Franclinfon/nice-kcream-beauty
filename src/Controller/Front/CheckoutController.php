<?php

namespace App\Controller\Front;

use App\Entity\Address;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Repository\AddressRepository;
use App\Service\CartService;
use App\Service\CheckoutService;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class CheckoutController extends AbstractController
{
    public function __construct(
        private string $stripeSecretKey,
    ) {
    }

    #[Route('/commande/adresse', name: 'app_front_checkout_address')]
    public function address(Request $request, AddressRepository $addressRepository, CheckoutService $checkoutService, CartService $cartService): Response
    {
        if (count($cartService->getCartItems()) === 0) {
            $this->addFlash('error', 'Votre panier est vide.');

            return $this->redirectToRoute('app_front_cart_index');
        }

        $user = $this->getUser();
        $addresses = $addressRepository->findBy(['client' => $user]);

        if ($request->isMethod('POST')) {
            $addressId = (int) $request->request->get('address_id');
            $checkoutService->setAddressId($addressId);

            return $this->redirectToRoute('app_front_checkout_delivery');
        }

        return $this->render('front/checkout/address.html.twig', [
            'addresses' => $addresses,
            'selectedAddressId' => $checkoutService->getAddressId(),
        ]);
    }

    #[Route('/commande/livraison', name: 'app_front_checkout_delivery')]
    public function delivery(Request $request, CheckoutService $checkoutService, CartService $cartService): Response
    {
        if (!$checkoutService->getAddressId()) {
            return $this->redirectToRoute('app_front_checkout_address');
        }

        $cartTotal = $cartService->getTotal();

        if ($request->isMethod('POST')) {
            $method = $request->request->get('delivery_method');
            $checkoutService->setDeliveryMethod($method);

            return $this->redirectToRoute('app_front_checkout_recap');
        }

        return $this->render('front/checkout/delivery.html.twig', [
            'cartTotal' => $cartTotal,
            'colissimoCost' => $checkoutService->calculateShippingCost('colissimo', $cartTotal),
            'selectedMethod' => $checkoutService->getDeliveryMethod(),
        ]);
    }

    #[Route('/commande/recapitulatif', name: 'app_front_checkout_recap')]
    public function recap(
        Request $request,
        AddressRepository $addressRepository,
        CheckoutService $checkoutService,
        CartService $cartService,
        EntityManagerInterface $entityManager,
    ): Response {
        $addressId = $checkoutService->getAddressId();
        $deliveryMethod = $checkoutService->getDeliveryMethod();

        if (!$addressId || !$deliveryMethod) {
            return $this->redirectToRoute('app_front_checkout_address');
        }

        $address = $addressRepository->find($addressId);
        $user = $this->getUser();

        if (!$address || $address->getClient() !== $user) {
            return $this->redirectToRoute('app_front_checkout_address');
        }

        $cartItems = $cartService->getCartItems();
        $cartTotal = $cartService->getTotal();
        $shippingCost = $checkoutService->calculateShippingCost($deliveryMethod, $cartTotal);

        if ($request->isMethod('POST')) {
            $pendingOrderId = $checkoutService->getPendingOrderId();
            $existingOrder = $pendingOrderId
                ? $entityManager->getRepository(Order::class)->find($pendingOrderId)
                : null;

            if ($existingOrder && $existingOrder->getStatut() === 'en_attente_paiement') {
                return $this->redirectToRoute('app_front_checkout_payment', ['id' => $existingOrder->getId()]);
            }

            $order = new Order();
            $order->setClient($user);
            $order->setNumeroCommande('CMD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6)));
            $order->setMontantTotal((string) ($cartTotal + $shippingCost));
            $order->setStatut('en_attente_paiement');
            $order->setDeliveryMethod($deliveryMethod);
            $order->setShippingCost((string) $shippingCost);

            $order->setLivraisonNom($user->getNom() . ' ' . $user->getPrenom());
            $order->setLivraisonRue($address->getRue());
            $order->setLivraisonComplement($address->getComplement());
            $order->setLivraisonCodePostal($address->getCodePostal());
            $order->setLivraisonVille($address->getVille());
            $order->setLivraisonPays($address->getPays());
            $order->setLivraisonTelephone($user->getTelephone() ?? '');
            $order->setLivraisonEmail($user->getEmail());

            foreach ($cartItems as $item) {
                $orderItem = new OrderItem();
                $orderItem->setProduct($item['product']);
                $orderItem->setNomProduit($item['product']->getNom());
                $orderItem->setPrixUnitaire((string) ($item['product']->getPrixPromo() ?? $item['product']->getPrix()));
                $orderItem->setQuantite($item['quantity']);
                $order->addOrderItem($orderItem);
            }

            $entityManager->persist($order);
            $entityManager->flush();

            $checkoutService->setPendingOrderId($order->getId());

            return $this->redirectToRoute('app_front_checkout_payment', ['id' => $order->getId()]);
        }

        return $this->render('front/checkout/recap.html.twig', [
            'address' => $address,
            'deliveryMethod' => $deliveryMethod,
            'cartItems' => $cartItems,
            'cartTotal' => $cartTotal,
            'shippingCost' => $shippingCost,
            'grandTotal' => $cartTotal + $shippingCost,
        ]);
    }

    #[Route('/commande/paiement/{id}', name: 'app_front_checkout_payment')]
    public function payment(
        int $id,
        EntityManagerInterface $entityManager,
    ): Response {
        $order = $entityManager->getRepository(Order::class)->find($id);
        $user = $this->getUser();

        if (!$order || $order->getClient() !== $user || $order->getStatut() !== 'en_attente_paiement') {
            return $this->redirectToRoute('app_front_home');
        }

        Stripe::setApiKey($this->stripeSecretKey);

        $lineItems = [];

        foreach ($order->getOrderItems() as $item) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $item->getNomProduit(),
                    ],
                    'unit_amount' => (int) round((float) $item->getPrixUnitaire() * 100),
                ],
                'quantity' => $item->getQuantite(),
            ];
        }

        if ((float) $order->getShippingCost() > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => 'Livraison Colissimo',
                    ],
                    'unit_amount' => (int) round((float) $order->getShippingCost() * 100),
                ],
                'quantity' => 1,
            ];
        }

        $session = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'customer_email' => $user->getEmail(),
            'success_url' => $this->generateUrl('app_front_checkout_success', ['id' => $order->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $this->generateUrl('app_front_checkout_cancel', ['id' => $order->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
            'metadata' => [
                'order_id' => $order->getId(),
                'order_numero' => $order->getNumeroCommande(),
            ],
        ]);

        $order->setStripePaymentIntentId($session->id);
        $entityManager->flush();

        return $this->redirect($session->url, 303);
    }

    #[Route('/commande/succes/{id}', name: 'app_front_checkout_success')]
    public function success(int $id, EntityManagerInterface $entityManager, CartService $cartService, CheckoutService $checkoutService): Response
    {
        $order = $entityManager->getRepository(Order::class)->find($id);
        $user = $this->getUser();

        if (!$order || $order->getClient() !== $user) {
            return $this->redirectToRoute('app_front_home');
        }

        if ($order->getStatut() === 'payee') {
            $cartService->clear();
            $checkoutService->clear();
        }

        return $this->render('front/checkout/success.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/commande/annule/{id}', name: 'app_front_checkout_cancel')]
    public function cancel(int $id, EntityManagerInterface $entityManager): Response
    {
        $order = $entityManager->getRepository(Order::class)->find($id);
        $user = $this->getUser();

        if (!$order || $order->getClient() !== $user) {
            return $this->redirectToRoute('app_front_home');
        }

        return $this->render('front/checkout/cancel.html.twig', [
            'order' => $order,
        ]);
    }
}