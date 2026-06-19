<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    public function index(): Response
    {
        return $this->redirect($this->adminUrlGenerator
            ->setController(ProductCrudController::class)
            ->setAction('index')
            ->generateUrl()
        );
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
        yield MenuItem::linkTo(OrderItemCrudController::class, 'Lignes de commande', 'fa fa-list');
        yield MenuItem::linkTo(OrderStatusHistoryCrudController::class, 'Historique statuts', 'fa fa-history');
        yield MenuItem::linkTo(AddressCrudController::class, 'Adresses', 'fa fa-map-marker-alt');
        yield MenuItem::section('Contenu');
        yield MenuItem::linkTo(BlogPostCrudController::class, 'Articles de blog', 'fa fa-newspaper');
        yield MenuItem::linkTo(BeforeAfterCrudController::class, 'Avant / Après', 'fa fa-images');
        yield MenuItem::linkTo(ServiceCrudController::class, 'Soins', 'fa fa-spa');
        yield MenuItem::linkTo(ReviewCrudController::class, 'Avis clients', 'fa fa-star');
        yield MenuItem::section('Messages');
        yield MenuItem::linkTo(ContactMessageCrudController::class, 'Messages de contact', 'fa fa-envelope');
        yield MenuItem::section('Utilisateurs');
        yield MenuItem::linkTo(UserCrudController::class, 'Utilisateurs', 'fa fa-users');
        yield MenuItem::section('Paramètres');
        yield MenuItem::linkTo(SiteSettingCrudController::class, 'Paramètres du site', 'fa fa-cog');
    }
}