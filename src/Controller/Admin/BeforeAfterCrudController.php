<?php

namespace App\Controller\Admin;

use App\Entity\BeforeAfter;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class BeforeAfterCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return BeforeAfter::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('titre', 'Titre'),
            TextareaField::new('description', 'Description')->setRequired(false)->hideOnIndex(),

            Field::new('imageAvantFile', 'Image AVANT')
                ->setFormType(FileType::class)
                ->setFormTypeOptions([
                    'mapped' => false,
                    'required' => false,
                    'attr' => ['accept' => 'image/jpeg,image/png,image/webp'],
                ])
                ->hideOnIndex(),

            Field::new('imageApresFile', 'Image APRÈS')
                ->setFormType(FileType::class)
                ->setFormTypeOptions([
                    'mapped' => false,
                    'required' => false,
                    'attr' => ['accept' => 'image/jpeg,image/png,image/webp'],
                ])
                ->hideOnIndex(),

            ImageField::new('imageAvant', 'Avant')
                ->setBasePath('/uploads/before_after')
                ->setUploadDir('public/uploads/before_after')
                ->onlyOnIndex(),

            ImageField::new('imageApres', 'Après')
                ->setBasePath('/uploads/before_after')
                ->setUploadDir('public/uploads/before_after')
                ->onlyOnIndex(),

            BooleanField::new('isActive', 'Actif'),
            IntegerField::new('position', 'Position')->setHelp('Ordre d\'affichage (0 = premier)'),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->handleUploads($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->handleUploads($entityInstance);
        parent::updateEntity($entityManager, $entityInstance);
    }

    private function handleUploads(BeforeAfter $entity): void
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $files = $request->files->all();

        $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/before_after';
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);
        }

        // Cherche les fichiers dans toutes les clés du formulaire
        $avantFile = null;
        $apresFile = null;

        foreach ($files as $formName => $formFiles) {
            if (is_array($formFiles)) {
                if (isset($formFiles['imageAvantFile']) && $formFiles['imageAvantFile'] instanceof UploadedFile) {
                    $avantFile = $formFiles['imageAvantFile'];
                }
                if (isset($formFiles['imageApresFile']) && $formFiles['imageApresFile'] instanceof UploadedFile) {
                    $apresFile = $formFiles['imageApresFile'];
                }
            }
        }

        if ($avantFile instanceof UploadedFile) {
            $filename = uniqid('avant_') . '.' . $avantFile->guessExtension();
            $avantFile->move($uploadsDir, $filename);
            $entity->setImageAvant($filename);
        }

        if ($apresFile instanceof UploadedFile) {
            $filename = uniqid('apres_') . '.' . $apresFile->guessExtension();
            $apresFile->move($uploadsDir, $filename);
            $entity->setImageApres($filename);
        }

        $entity->setUpdatedAt(new \DateTimeImmutable());
    }
}