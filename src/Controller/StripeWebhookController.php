<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderStatusHistory;
use App\Service\CartService;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
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
    public function webhook(
        Request $request,
        EntityManagerInterface $entityManager,
        CartService $cartService,
        MailerService $mailerService,
        LoggerInterface $logger,
    ): Response {
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
                return new Response('Déjà traitée.', 200);
            }

            $order->setStatut('payee');

            $history = new OrderStatusHistory();
            $history->setCommande($order);
            $history->setStatut('payee');
            $history->setDate(new \DateTimeImmutable());
            $history->setCommentaire('Paiement confirmé via Stripe Checkout.');
            $entityManager->persist($history);
            $entityManager->flush();

            $logger->info('[WEBHOOK] Commande payée : ' . $order->getNumeroCommande());

            // Email client
            try {
                $mailerService->sendOrderConfirmationToClient($order);
                $logger->info('[WEBHOOK] Email client envoyé à : ' . $order->getLivraisonEmail());
            } catch (\Exception $e) {
                $logger->error('[WEBHOOK] Erreur email client : ' . $e->getMessage());
            }

            // Email admin
            try {
                $mailerService->sendOrderNotificationToAdmin($order);
                $logger->info('[WEBHOOK] Email admin envoyé.');
            } catch (\Exception $e) {
                $logger->error('[WEBHOOK] Erreur email admin : ' . $e->getMessage());
            }
        }

        return new Response('OK', 200);
    }
}