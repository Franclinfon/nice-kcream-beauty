<?php

namespace App\Service;

use App\Entity\Order;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Twig\Environment;
use App\Entity\ContactMessage;

class MailerService
{
    public function __construct(
        private MailerInterface $mailer,
        private Environment $twig,
        private string $mailerFrom,
        private string $mailerAdmin1,
        private string $mailerAdmin2,
    ) {
    }

    public function sendOrderConfirmationToClient(Order $order): void
    {
        $html = $this->twig->render('emails/order_confirmation_client.html.twig', [
            'order' => $order,
        ]);

        $email = (new Email())
            ->from(new Address($this->mailerFrom, 'Nice K\'Cream Beauty'))
            ->to($order->getLivraisonEmail())
            ->subject('Confirmation de votre commande ' . $order->getNumeroCommande())
            ->html($html);

        $this->mailer->send($email);
    }

    public function sendOrderNotificationToAdmin(Order $order): void
    {
        $html = $this->twig->render('emails/order_notification_admin.html.twig', [
            'order' => $order,
        ]);

        $subject = '🛍 Nouvelle commande ' . $order->getNumeroCommande();

        // Email vers admin 1
        $email1 = (new Email())
            ->from(new Address($this->mailerFrom, 'Nice K\'Cream Beauty'))
            ->to($this->mailerAdmin1)
            ->subject($subject)
            ->html($html);

        $this->mailer->send($email1);

        // Email vers admin 2 si différent
        if ($this->mailerAdmin2 !== $this->mailerAdmin1) {
            $email2 = (new Email())
                ->from(new Address($this->mailerFrom, 'Nice K\'Cream Beauty'))
                ->to($this->mailerAdmin2)
                ->subject($subject)
                ->html($html);

            $this->mailer->send($email2);
        }
    }

    public function sendContactNotificationToAdmin(ContactMessage $contact): void
    {
        $html = $this->twig->render('emails/contact_notification_admin.html.twig', [
            'contact' => $contact,
        ]);

        $subject = '📩 Nouveau message de contact — ' . $contact->getSujet();

        $email1 = (new Email())
            ->from(new Address($this->mailerFrom, 'Nice K\'Cream Beauty'))
            ->to($this->mailerAdmin1)
            ->replyTo($contact->getEmail())
            ->subject($subject)
            ->html($html);

        $this->mailer->send($email1);

        if ($this->mailerAdmin2 !== $this->mailerAdmin1) {
            $email2 = (new Email())
                ->from(new Address($this->mailerFrom, 'Nice K\'Cream Beauty'))
                ->to($this->mailerAdmin2)
                ->replyTo($contact->getEmail())
                ->subject($subject)
                ->html($html);

            $this->mailer->send($email2);
        }
    }

}