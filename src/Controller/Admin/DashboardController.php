<?php

namespace App\Controller\Admin;

use App\Controller\Admin\CategoryCrudController;
use App\Controller\Admin\OrderCrudController;
use App\Controller\Admin\ProductCrudController;
use App\Controller\Admin\ProductImageCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        return parent::index();
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Nice K\'Cream Beauty');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::section('Catalogue');
        yield MenuItem::linkTo(CategoryCrudController::class, 'Catégories', 'fa fa-folder');
        yield MenuItem::linkTo(ProductCrudController::class, 'Produits', 'fa fa-spa');
        yield MenuItem::linkTo(ProductImageCrudController::class, 'Images produits', 'fa fa-image');

        yield MenuItem::section('Ventes');
        yield MenuItem::linkTo(OrderCrudController::class, 'Commandes', 'fa fa-shopping-cart');
    }
}