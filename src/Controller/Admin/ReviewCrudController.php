<?php

namespace App\Controller\Admin;

use App\Entity\Review;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ReviewCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Review::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),

            TextField::new('auteur', 'Nom du client')
                ->setHelp('Ex: Sophie L., Camille D.'),

            ChoiceField::new('note', 'Note')
                ->setChoices([
                    '⭐ 1 étoile'     => 1,
                    '⭐⭐ 2 étoiles'   => 2,
                    '⭐⭐⭐ 3 étoiles'  => 3,
                    '⭐⭐⭐⭐ 4 étoiles' => 4,
                    '⭐⭐⭐⭐⭐ 5 étoiles' => 5,
                ])
                ->renderAsBadges([
                    1 => 'warning',
                    2 => 'warning',
                    3 => 'warning',
                    4 => 'success',
                    5 => 'success',
                ]),

            TextareaField::new('commentaire', 'Commentaire')
                ->setNumOfRows(4)
                ->setHelp('Copiez le commentaire Google de la cliente'),

            BooleanField::new('isVisible', 'Visible sur le site'),

            DateTimeField::new('createdAt', 'Date')->hideOnForm(),
        ];
    }
}