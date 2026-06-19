<?php

namespace App\Controller\Front;

use App\Repository\BeforeAfterRepository;
use App\Repository\BlogPostRepository;
use App\Repository\ProductRepository;
use App\Repository\ReviewRepository;
use App\Repository\ServiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_front_home')]
    public function index(
        ProductRepository $productRepository,
        ServiceRepository $serviceRepository,
        ReviewRepository $reviewRepository,
        BeforeAfterRepository $beforeAfterRepository,
        BlogPostRepository $blogPostRepository,
    ): Response {
        $featuredProducts = $productRepository->findBy(
            ['isActive' => true, 'isMiseEnAvant' => true],
            ['createdAt' => 'DESC'],
            5
        );

        $services = $serviceRepository->findBy(
            ['isPublished' => true],
            ['position' => 'ASC']
        );

        $reviews = $reviewRepository->findBy(
            ['statut' => 'approuve'],
            ['createdAt' => 'DESC'],
            4
        );

        $beforeAfters = $beforeAfterRepository->findBy(
            ['isActive' => true],
            ['position' => 'ASC'],
            3
        );

        $blogPosts = $blogPostRepository->findBy(
            ['isPublished' => true],
            ['publishedAt' => 'DESC'],
            3
        );

        return $this->render('front/home/index.html.twig', [
            'featuredProducts' => $featuredProducts,
            'services' => $services,
            'reviews' => $reviews,
            'beforeAfters' => $beforeAfters,
            'blogPosts' => $blogPosts,
        ]);
    }
}