<?php

namespace App\Controller\Front;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\OrderStatusHistory;
use App\Repository\AddressRepository;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CheckoutController extends AbstractController
{
    public function __construct(
        private string $stripePublicKey,
        private string $stripeSecretKey,
    ) {
    }

    #[Route('/commande/adresse', name: 'app_front_checkout_address')]
    public function address(Request $request, AddressRepository $addressRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();
        $addresses = $addressRepository->findBy(['client' => $user]);

        if ($request->isMethod('POST')) {
            $addressId = $request->request->get('address_id');
            if ($addressId) {
                $request->getSession()->set('checkout_address_id', $addressId);
                return $this->redirectToRoute('app_front_checkout_delivery');
            }
        }

        $selectedAddressId = $request->getSession()->get('checkout_address_id');

        return $this->render('front/checkout/address.html.twig', [
            'addresses' => $addresses,
            'selectedAddressId' => $selectedAddressId,
        ]);
    }

    #[Route('/commande/livraison', name: 'app_front_checkout_delivery')]
    public function delivery(Request $request, CartService $cartService): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if (!$request->getSession()->get('checkout_address_id')) {
            return $this->redirectToRoute('app_front_checkout_address');
        }

        if ($request->isMethod('POST')) {
            $method = $request->request->get('delivery_method', 'colissimo');
            $request->getSession()->set('checkout_delivery_method', $method);
            return $this->redirectToRoute('app_front_checkout_recap');
        }

        $total = $cartService->getTotal();
        $colissimoCost = $total >= 70 ? 0.0 : 5.90;
        $selectedMethod = $request->getSession()->get('checkout_delivery_method');

        return $this->render('front/checkout/delivery.html.twig', [
            'selectedMethod' => $selectedMethod,
            'colissimoCost'  => $colissimoCost,
            'total'          => $total,
        ]);
    }

    #[Route('/commande/recapitulatif', name: 'app_front_checkout_recap', methods: ['GET', 'POST'])]
    public function recap(
        Request $request,
        CartService $cartService,
        AddressRepository $addressRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $session = $request->getSession();
        $addressId = $session->get('checkout_address_id');
        $deliveryMethod = $session->get('checkout_delivery_method', 'colissimo');

        if (!$addressId) {
            return $this->redirectToRoute('app_front_checkout_address');
        }

        $address = $addressRepository->find($addressId);
        $cartItems = $cartService->getCartItems();
        $total = $cartService->getTotal();
        $shippingCost = ($deliveryMethod === 'colissimo' && $total < 70) ? 5.90 : 0.0;
        $totalWithShipping = $total + $shippingCost;

        if ($request->isMethod('POST')) {
            $user = $this->getUser();

            // Protection anti-doublon
            $pendingOrderId = $session->get('pendingOrderId');
            if ($pendingOrderId) {
                return $this->redirectToRoute('app_front_checkout_payment_redirect', [
                    'id' => $pendingOrderId,
                ]);
            }

            $order = new Order();
            $order->setClient($user);
            $order->setNumeroCommande('CMD-' . strtoupper(date('Ymd')) . '-' . strtoupper(substr(uniqid(), -6)));
            $order->setMontantTotal((string) $totalWithShipping);
            $order->setStatut('en_attente_paiement');
            $order->setDeliveryMethod($deliveryMethod);
            $order->setShippingCost((string) $shippingCost);

            // Adresse de livraison
            $order->setLivraisonNom($user->getPrenom() . ' ' . $user->getNom());
            $order->setLivraisonRue($address->getRue());
            $order->setLivraisonComplement($address->getComplement());
            $order->setLivraisonCodePostal($address->getCodePostal());
            $order->setLivraisonVille($address->getVille());
            $order->setLivraisonPays($address->getPays());
            $order->setLivraisonTelephone($user->getTelephone() ?? '');
            $order->setLivraisonEmail($user->getEmail());

            // Articles — produits ET coffrets
            foreach ($cartItems as $item) {
                $orderItem = new OrderItem();

                if ($item['type'] === 'coffret') {
                    $coffret = $item['coffret'];
                    $orderItem->setNomProduit('Coffret : ' . $coffret->getNom());
                    $orderItem->setPrixUnitaire((string) $coffret->getPrix());
                } else {
                    $product = $item['product'];
                    $orderItem->setProduct($product);
                    $orderItem->setNomProduit($product->getNom());
                    $orderItem->setPrixUnitaire((string) ($product->getPrixPromo() ?? $product->getPrix()));
                }

                $orderItem->setQuantite($item['quantity']);
                $order->addOrderItem($orderItem);
            }

            $entityManager->persist($order);
            $entityManager->flush();

            $session->set('pendingOrderId', $order->getId());

            // Stripe Checkout
            Stripe::setApiKey($this->stripeSecretKey);

            $lineItems = [];
            foreach ($cartItems as $item) {
                if ($item['type'] === 'coffret') {
                    $lineItems[] = [
                        'price_data' => [
                            'currency' => 'eur',
                            'product_data' => [
                                'name' => 'Coffret : ' . $item['coffret']->getNom(),
                            ],
                            'unit_amount' => (int) round((float) $item['coffret']->getPrix() * 100),
                        ],
                        'quantity' => $item['quantity'],
                    ];
                } else {
                    $prix = $item['product']->getPrixPromo() ?? $item['product']->getPrix();
                    $lineItems[] = [
                        'price_data' => [
                            'currency' => 'eur',
                            'product_data' => [
                                'name' => $item['product']->getNom(),
                            ],
                            'unit_amount' => (int) round((float) $prix * 100),
                        ],
                        'quantity' => $item['quantity'],
                    ];
                }
            }

            // Frais de livraison
            if ($shippingCost > 0) {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => ['name' => 'Livraison Colissimo'],
                        'unit_amount' => (int) round($shippingCost * 100),
                    ],
                    'quantity' => 1,
                ];
            }

            $checkoutSession = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'customer_email' => $user->getEmail(),
                'success_url' => $this->generateUrl('app_front_checkout_success', ['orderId' => $order->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                'cancel_url' => $this->generateUrl('app_front_checkout_recap', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'metadata' => ['order_id' => $order->getId()],
            ]);

            return $this->redirect($checkoutSession->url);
        }

        return $this->render('front/checkout/recap.html.twig', [
            'cartItems'        => $cartItems,
            'address'          => $address,
            'deliveryMethod'   => $deliveryMethod,
            'shippingCost'     => $shippingCost,
            'total'            => $total,
            'totalWithShipping' => $totalWithShipping,
        ]);
    }

    #[Route('/commande/succes/{orderId}', name: 'app_front_checkout_success')]
    public function success(int $orderId, Request $request, EntityManagerInterface $entityManager): Response
    {
        $request->getSession()->remove('pendingOrderId');

        $order = $entityManager->getRepository(Order::class)->find($orderId);

        return $this->render('front/checkout/success.html.twig', [
            'order' => $order,
        ]);
    }
}