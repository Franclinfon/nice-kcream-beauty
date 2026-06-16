<?php

namespace App\Controller\Front;

use App\Entity\Product;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProductController extends AbstractController
{
    #[Route('/boutique', name: 'app_front_product_index')]
    public function index(Request $request, ProductRepository $productRepository, CategoryRepository $categoryRepository): Response
    {
        $categorySlug = $request->query->get('categorie');

        $criteria = ['isActive' => true];

        if ($categorySlug) {
            $category = $categoryRepository->findOneBy(['slug' => $categorySlug]);
            if ($category) {
                $criteria['category'] = $category;
            }
        }

        $products = $productRepository->findBy($criteria, ['createdAt' => 'DESC']);
        $categories = $categoryRepository->findAll();

        return $this->render('front/product/index.html.twig', [
            'products' => $products,
            'categories' => $categories,
            'currentCategorySlug' => $categorySlug,
        ]);
    }

    #[Route('/boutique/{slug}', name: 'app_front_product_show')]
    public function show(string $slug, ProductRepository $productRepository): Response
    {
        $product = $productRepository->findOneBy(['slug' => $slug, 'isActive' => true]);

        if (!$product) {
            throw $this->createNotFoundException('Produit introuvable.');
        }

        return $this->render('front/product/show.html.twig', [
            'product' => $product,
        ]);
    }
}