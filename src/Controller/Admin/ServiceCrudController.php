<?php

namespace App\Controller\Admin;

use App\Entity\Service;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ServiceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Service::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Soin')
            ->setEntityLabelInPlural('Soins')
            ->setDefaultSort(['position' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addFieldset('Informations générales');
        yield TextField::new('titre');
        yield SlugField::new('slug')->setTargetFieldName('titre');
        yield ChoiceField::new('categorie')->setChoices([
            'Visage' => 'visage',
            'Épilation' => 'epilation',
            'Massage' => 'massage',
            'Autre' => 'autre',
        ]);
        yield TextareaField::new('description')->hideOnIndex();
        yield TextEditorField::new('contenu')->hideOnIndex()->setRequired(false);

        yield FormField::addFieldset('Image');
        yield Field::new('imageFile', 'Photo')
            ->setFormType(VichImageType::class)
            ->onlyOnForms();
        yield ImageField::new('image', 'Aperçu')
            ->setBasePath('uploads/services')
            ->setUploadDir('public/uploads/services')
            ->onlyOnIndex();

        yield FormField::addFieldset('Prestation');
        yield MoneyField::new('prix')->setCurrency('EUR')->setStoredAsCents(false)->setRequired(false);
        yield IntegerField::new('duree', 'Durée (minutes)')->setRequired(false);
        yield UrlField::new('lienFresha', 'Lien Fresha')->hideOnIndex()->setRequired(false);

        yield FormField::addFieldset('Affichage');
        yield BooleanField::new('isPublished', 'Publié');
        yield IntegerField::new('position');

        yield IdField::new('id')->hideOnForm();
        yield TextField::new('metaDescription')->hideOnIndex()->setRequired(false);
    }
}