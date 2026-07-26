<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AnalysePeauController extends AbstractController
{
    #[Route('/analyse-de-peau', name: 'app_front_analyse_peau')]
    public function index(): Response
    {
        return $this->render('front/analyse_peau/index.html.twig');
    }
}