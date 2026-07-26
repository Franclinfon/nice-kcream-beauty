<?php

namespace App\Controller\Admin;

use App\Entity\BlogPost;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Vich\UploaderBundle\Form\Type\VichImageType;

class BlogPostCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return BlogPost::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('titre', 'Titre'),
            TextField::new('slug', 'Slug'),
            TextareaField::new('contenu', 'Contenu')->hideOnIndex(),
            Field::new('imageFile', 'Image')
                ->setFormType(VichImageType::class)
                ->hideOnIndex(),
            ImageField::new('image', 'Aperçu')
                ->setBasePath('/uploads/blog')
                ->setUploadDir('public/uploads/blog')
                ->onlyOnIndex(),
            TextField::new('categorie', 'Catégorie')->setRequired(false),
            BooleanField::new('isPublished', 'Publié'),
            DateTimeField::new('publishedAt', 'Publié le')->setRequired(false),
            DateTimeField::new('createdAt', 'Créé le')->hideOnForm(),
        ];
    }
}