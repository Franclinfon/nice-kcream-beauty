<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Produit')
            ->setEntityLabelInPlural('Produits')
            ->setDefaultSort(['createdAt' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addFieldset('Informations générales');
        yield TextField::new('nom');
        yield SlugField::new('slug')->setTargetFieldName('nom');
        yield AssociationField::new('category', 'Catégorie');
        yield TextareaField::new('description')->hideOnIndex();
        yield TextareaField::new('conseilsUtilisation', 'Conseils d\'utilisation')->hideOnIndex()->setRequired(false);

        yield FormField::addFieldset('Prix et stock');
        yield MoneyField::new('prix')->setCurrency('EUR')->setStoredAsCents(false);
        yield MoneyField::new('prixPromo', 'Prix promo')->setCurrency('EUR')->setStoredAsCents(false)->setRequired(false)->hideOnIndex();
        yield DateTimeField::new('dateDebutPromo', 'Début promo')->hideOnIndex()->setRequired(false);
        yield DateTimeField::new('dateFinPromo', 'Fin promo')->hideOnIndex()->setRequired(false);
        yield IntegerField::new('stockQuantity', 'Stock')->setRequired(false);
        yield BooleanField::new('isRupture', 'Rupture de stock');

        yield FormField::addFieldset('Mise en avant');
        yield BooleanField::new('isNouveaute', 'Nouveauté');
        yield BooleanField::new('isMiseEnAvant', 'Mise en avant');
        yield BooleanField::new('isCoffret', 'Coffret');
        yield BooleanField::new('isActive', 'Actif (visible sur le site)');

        yield IdField::new('id')->hideOnForm();
        yield DateTimeField::new('createdAt', 'Créé le')->hideOnForm();
    }
}