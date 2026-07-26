<?php

namespace App\Controller\Front;

use App\Entity\ContactMessage;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_front_contact')]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        MailerService $mailerService,
    ): Response {
        $error = null;
        $success = false;

        if ($request->isMethod('POST')) {
            $nom = trim($request->request->get('nom', ''));
            $email = trim($request->request->get('email', ''));
            $sujet = trim($request->request->get('sujet', ''));
            $message = trim($request->request->get('message', ''));

            if (!$nom || !$email || !$sujet || !$message) {
                $error = 'Veuillez remplir tous les champs.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'L\'adresse email n\'est pas valide.';
            } else {
                // Sauvegarde en base
                $contact = new ContactMessage();
                $contact->setNom($nom);
                $contact->setEmail($email);
                $contact->setSujet($sujet);
                $contact->setMessage($message);
                $contact->setCreatedAt(new \DateTimeImmutable());
                $entityManager->persist($contact);
                $entityManager->flush();

                // Email à l'admin
                try {
                    $mailerService->sendContactNotificationToAdmin($contact);
                } catch (\Exception $e) {
                    // Ne pas bloquer si l'email échoue
                }

                $success = true;
            }
        }

        return $this->render('front/contact/index.html.twig', [
            'error'   => $error,
            'success' => $success,
        ]);
    }
}