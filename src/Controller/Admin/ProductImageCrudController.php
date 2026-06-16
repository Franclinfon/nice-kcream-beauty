<?php

namespace App\Controller\Admin;

use App\Entity\ProductImage;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ProductImageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ProductImage::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Image produit')
            ->setEntityLabelInPlural('Images produits')
            ->setDefaultSort(['product' => 'ASC', 'position' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('product', 'Produit');
        yield Field::new('imageFile', 'Image')
            ->setFormType(VichImageType::class)
            ->onlyOnForms();
        yield ImageField::new('filename', 'Aperçu')
            ->setBasePath('uploads/products')
            ->setUploadDir('public/uploads/products')
            ->onlyOnIndex();
        yield IntegerField::new('position');
        yield BooleanField::new('isMain', 'Image principale');
        yield IdField::new('id')->hideOnForm();
    }
}