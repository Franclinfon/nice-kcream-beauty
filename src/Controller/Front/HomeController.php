<?php

namespace App\Controller\Front;

use App\Repository\ProductRepository;
use App\Repository\ServiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_front_home')]
    public function index(ProductRepository $productRepository, ServiceRepository $serviceRepository): Response
    {
        $featuredProducts = $productRepository->findBy(
            ['isActive' => true, 'isMiseEnAvant' => true],
            ['createdAt' => 'DESC'],
            5
        );

        $services = $serviceRepository->findBy(
            ['isPublished' => true],
            ['position' => 'ASC']
        );

        return $this->render('front/home/index.html.twig', [
            'featuredProducts' => $featuredProducts,
            'services' => $services,
        ]);
    }
}