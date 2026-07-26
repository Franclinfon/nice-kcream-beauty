<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LegalController extends AbstractController
{
    #[Route('/mentions-legales', name: 'app_front_mentions_legales')]
    public function mentionsLegales(): Response
    {
        return $this->render('front/legal/mentions_legales.html.twig');
    }

    #[Route('/conditions-generales-de-vente', name: 'app_front_cgv')]
    public function cgv(): Response
    {
        return $this->render('front/legal/cgv.html.twig');
    }

    #[Route('/politique-de-confidentialite', name: 'app_front_confidentialite')]
    public function confidentialite(): Response
    {
        return $this->render('front/legal/confidentialite.html.twig');
    }

    #[Route('/politique-des-cookies', name: 'app_front_cookies')]
    public function cookies(): Response
    {
        return $this->render('front/legal/cookies.html.twig');
    }
}