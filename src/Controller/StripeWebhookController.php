<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderStatusHistory;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Webhook;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class StripeWebhookController extends AbstractController
{
    public function __construct(
        private string $stripeSecretKey,
        private string $stripeWebhookSecret,
    ) {
    }

    #[Route('/stripe/webhook', name: 'app_stripe_webhook', methods: ['POST'])]
    public function webhook(Request $request, EntityManagerInterface $entityManager, CartService $cartService): Response
    {
        $payload = $request->getContent();
        $sigHeader = $request->headers->get('Stripe-Signature');

        try {
            Stripe::setApiKey($this->stripeSecretKey);
            $event = Webhook::constructEvent($payload, $sigHeader, $this->stripeWebhookSecret);
        } catch (SignatureVerificationException $e) {
            return new Response('Signature invalide.', 400);
        } catch (\Exception $e) {
            return new Response('Erreur webhook : ' . $e->getMessage(), 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;

            $orderId = $session->metadata->order_id ?? null;

            if (!$orderId) {
                return new Response('order_id manquant dans metadata.', 400);
            }

            $order = $entityManager->getRepository(Order::class)->find($orderId);

            if (!$order) {
                return new Response('Commande introuvable.', 404);
            }

            if ($order->getStatut() === 'payee') {
                // Déjà traitée, on répond 200 pour éviter les retentatives Stripe
                return new Response('Déjà traitée.', 200);
            }

            // Mise à jour du statut de la commande
            $order->setStatut('payee');

            // Historique du statut
            $history = new OrderStatusHistory();
            $history->setCommande($order);
            $history->setStatut('payee');
            $history->setDate(new \DateTimeImmutable());
            $history->setCommentaire('Paiement confirmé via Stripe Checkout.');
            $entityManager->persist($history);

            $entityManager->flush();

            // Note : le panier est lié à la session HTTP du client, pas accessible ici côté webhook.
            // Il sera vidé côté front sur la page de succès via un flag en session.
        }

        return new Response('OK', 200);
    }
}