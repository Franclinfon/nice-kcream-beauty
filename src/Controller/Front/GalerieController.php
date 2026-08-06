<?php

namespace App\Controller\Front;

use App\Repository\BeforeAfterRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GalerieController extends AbstractController
{
    #[Route('/galerie-avant-apres', name: 'app_front_galerie')]
    public function index(BeforeAfterRepository $beforeAfterRepository): Response
    {
        $beforeAfters = $beforeAfterRepository->findBy(
            ['isActive' => true],
            ['position' => 'ASC']
        );

        return $this->render('front/galerie/index.html.twig', [
            'beforeAfters' => $beforeAfters,
        ]);
    }
}