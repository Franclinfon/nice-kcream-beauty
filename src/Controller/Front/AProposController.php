<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AProposController extends AbstractController
{
    #[Route('/a-propos', name: 'app_front_a_propos')]
    public function index(): Response
    {
        return $this->render('front/a_propos/index.html.twig');
    }
}