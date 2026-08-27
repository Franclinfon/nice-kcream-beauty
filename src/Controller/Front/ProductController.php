<?php

namespace App\Controller\Front;

use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProductController extends AbstractController
{
    #[Route('/boutique', name: 'app_front_product_index')]
    public function index(
        Request $request,
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        PaginatorInterface $paginator,
    ): Response {
        $categorySlug = $request->query->get('categorie');
        $search = $request->query->get('q');
        $sort = $request->query->get('tri', 'nouveautes');
        $nouveautesOnly = $request->query->getBoolean('nouveautes');
        $promosOnly = $request->query->getBoolean('promos');

        $productsQuery = $productRepository->findFilteredQuery(
            categorySlug: $categorySlug,
            search: $search,
            sort: $sort,
            nouveautesOnly: $nouveautesOnly,
            promosOnly: $promosOnly,
        );

        $pagination = $paginator->paginate(
            $productsQuery,
            $request->query->getInt('page', 1),
            12
        );

        $categories = $categoryRepository->findAll();

        return $this->render('front/product/index.html.twig', [
            'pagination'          => $pagination,
            'categories'          => $categories,
            'currentCategorySlug' => $categorySlug,
            'currentSearch'       => $search,
            'currentSort'         => $sort,
            'nouveautesOnly'      => $nouveautesOnly,
            'promosOnly'          => $promosOnly,
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