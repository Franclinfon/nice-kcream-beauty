<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Entity\ProductImage;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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
        yield TextareaField::new('conseilsUtilisation', 'Conseils d\'utilisation')
            ->hideOnIndex()
            ->setRequired(false);

        yield FormField::addFieldset('Image principale');
        yield ImageField::new('mainImageFilename', 'Image actuelle')
            ->setBasePath('/uploads/products')
            ->setUploadDir('public/uploads/products')
            ->onlyOnIndex()
            ->formatValue(function ($value, $entity) {
                $main = $entity->getMainImage();
                return $main ? $main->getFilename() : null;
            });
        yield Field::new('imageFile', 'Photo du produit')
            ->setFormType(FileType::class)
            ->setFormTypeOptions([
                'mapped' => false,
                'required' => false,
                'attr' => ['accept' => 'image/jpeg,image/png,image/webp'],
            ])
            ->hideOnIndex()
            ->setHelp('Formats acceptés : JPG, PNG, WEBP');

        yield FormField::addFieldset('Prix et stock');
        yield MoneyField::new('prix')->setCurrency('EUR')->setStoredAsCents(false);
        yield MoneyField::new('prixPromo', 'Prix promo')
            ->setCurrency('EUR')
            ->setStoredAsCents(false)
            ->setRequired(false)
            ->hideOnIndex();
        yield DateTimeField::new('dateDebutPromo', 'Début promo')->hideOnIndex()->setRequired(false);
        yield DateTimeField::new('dateFinPromo', 'Fin promo')->hideOnIndex()->setRequired(false);
        yield IntegerField::new('stockQuantity', 'Stock')->setRequired(false);
        yield BooleanField::new('isRupture', 'Rupture de stock');

        yield FormField::addFieldset('Mise en avant');
        yield BooleanField::new('isNouveaute', 'Nouveauté');
        yield BooleanField::new('isMiseEnAvant', 'Mise en avant');
        yield BooleanField::new('isPromo', 'En promotion');
        yield BooleanField::new('isCoffret', 'Coffret');
        yield BooleanField::new('isActive', 'Actif (visible sur le site)');

        yield IdField::new('id')->hideOnForm();
        yield DateTimeField::new('createdAt', 'Créé le')->hideOnForm();
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        // Validation doublon
        $existing = $entityManager->getRepository(Product::class)->findOneBy([
            'nom' => $entityInstance->getNom(),
        ]);

        if ($existing) {
            $this->addFlash('danger', 'Ce produit existe déjà, veuillez insérer un autre.');
            return;
        }

        parent::persistEntity($entityManager, $entityInstance);
        $this->handleImageUpload($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        parent::updateEntity($entityManager, $entityInstance);
        $this->handleImageUpload($entityManager, $entityInstance);
    }

    private function handleImageUpload(EntityManagerInterface $entityManager, Product $product): void
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();

        // EasyAdmin place les données du formulaire sous la clé du nom de l'entité
        $files = $request->files->all();
        $uploadedFile = null;

        foreach ($files as $formName => $formFiles) {
            if (is_array($formFiles) && isset($formFiles['imageFile'])) {
                $uploadedFile = $formFiles['imageFile'];
                break;
            }
        }

        if (!$uploadedFile instanceof UploadedFile) {
            return;
        }

        $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/products';
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);
        }

        $filename = uniqid('product_') . '.' . $uploadedFile->guessExtension();
        $uploadedFile->move($uploadsDir, $filename);

        // Désactiver les autres images principales
        foreach ($product->getProductImages() as $existingImage) {
            $existingImage->setIsMain(false);
            $entityManager->persist($existingImage);
        }

        $productImage = new ProductImage();
        $productImage->setFilename($filename);
        $productImage->setIsMain(true);
        $productImage->setPosition(1);
        $productImage->setProduct($product);
        $productImage->setUpdatedAt(new \DateTimeImmutable());

        $entityManager->persist($productImage);
        $entityManager->flush();
    }
}