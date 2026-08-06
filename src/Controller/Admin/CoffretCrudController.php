<?php

namespace App\Controller\Admin;

use App\Entity\Coffret;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CoffretCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Coffret::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('nom', 'Nom du coffret'),
            TextareaField::new('description', 'Description')->setRequired(false)->hideOnIndex(),
            MoneyField::new('prix', 'Prix')->setCurrency('EUR')->setStoredAsCents(false),
            AssociationField::new('products', 'Produits contenus')
                ->setFormTypeOption('by_reference', false)
                ->hideOnIndex(),
            BooleanField::new('isActive', 'Actif'),
            DateTimeField::new('createdAt', 'Créé le')->hideOnForm(),
        ];
    }
}