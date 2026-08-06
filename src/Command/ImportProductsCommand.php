<?php

namespace App\Command;

use App\Entity\Category;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\String\Slugger\SluggerInterface;

#[AsCommand(
    name: 'app:import-products',
    description: 'Importe les produits depuis un fichier CSV SumUp',
)]
class ImportProductsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SluggerInterface $slugger,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('file', InputArgument::REQUIRED, 'Chemin vers le fichier CSV');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $file = $input->getArgument('file');

        if (!file_exists($file)) {
            $io->error("Fichier introuvable : $file");
            return Command::FAILURE;
        }

        $handle = fopen($file, 'r');
        $headers = null;
        $imported = 0;
        $skipped = 0;
        $categories = [];
        $allSlugs = [];

        // Charger catégories existantes
        $existingCategories = $this->entityManager->getRepository(Category::class)->findAll();
        foreach ($existingCategories as $cat) {
            $categories[strtolower(trim($cat->getNom()))] = $cat;
        }

        // Charger produits existants pour éviter les doublons
        $existingProducts = $this->entityManager->getRepository(Product::class)->findAll();
        $existingNames = [];
        foreach ($existingProducts as $p) {
            $existingNames[strtolower(trim($p->getNom()))] = true;
            $allSlugs[$p->getSlug()] = true;
        }

        $batchSize = 20;
        $count = 0;

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            // Première ligne = headers
            if ($headers === null) {
                $headers = array_map('trim', $row);
                continue;
            }

            if (count($row) < count($headers)) {
                $row = array_pad($row, count($headers), '');
            }

            $data = array_combine($headers, $row);

            $nom = trim($data['Item name'] ?? '');
            $prix = trim($data['Price'] ?? '');
            $categoryName = trim($data['Category'] ?? '');
            $description = trim($data['Description (Online Store and Invoices only)'] ?? '');
            $quantity = trim($data['Quantity'] ?? '0');
            $regularPrice = trim($data['Regular price (before sale)'] ?? '');

            // Ignorer lignes vides
            if (empty($nom)) {
                $skipped++;
                continue;
            }

            // Valider le prix
            $prixClean = str_replace(',', '.', $prix);
            if (!is_numeric($prixClean) || (float)$prixClean <= 0) {
                $io->writeln("<comment>Prix invalide ignoré : $nom (prix: $prix)</comment>");
                $skipped++;
                continue;
            }

            // Ignorer doublons
            if (isset($existingNames[strtolower($nom)])) {
                $io->writeln("<comment>Doublon ignoré : $nom</comment>");
                $skipped++;
                continue;
            }

            // Catégorie
            $catKey = strtolower($categoryName);
            if (!empty($categoryName) && !isset($categories[$catKey])) {
                $category = new Category();
                $category->setNom($categoryName);
                $categorySlug = strtolower($this->slugger->slug($categoryName)->toString());
                // Slug unique pour catégorie
                $catSlugFinal = $categorySlug;
                $j = 1;
                while (isset($allSlugs['cat_' . $catSlugFinal])) {
                    $catSlugFinal = $categorySlug . '-' . $j++;
                }
                $allSlugs['cat_' . $catSlugFinal] = true;
                $category->setSlug($catSlugFinal);
                $this->entityManager->persist($category);
                $categories[$catKey] = $category;
                $io->writeln("<info>Nouvelle catégorie : $categoryName</info>");
            }

            $category = !empty($catKey) && isset($categories[$catKey]) ? $categories[$catKey] : null;

            // Slug unique pour produit
            $baseSlug = strtolower($this->slugger->slug($nom)->toString());
            $slug = $baseSlug;
            $i = 1;
            while (isset($allSlugs[$slug])) {
                $slug = $baseSlug . '-' . $i++;
            }
            $allSlugs[$slug] = true;

            // Stock
            $quantityInt = is_numeric($quantity) ? (int)$quantity : 0;
            if ($quantityInt < 0) $quantityInt = 0;

            // Produit
            $product = new Product();
            $product->setNom($nom);
            $product->setSlug($slug);
            $product->setDescription(!empty($description) ? $description : null);
            $product->setStockQuantity($quantityInt);
            $product->setIsActive(true);
            $product->setIsNouveaute(false);
            $product->setIsMiseEnAvant(false);
            $product->setIsPromo(false);
            $product->setIsCoffret(false);
            $product->setIsRupture($quantityInt <= 0);
            $product->setCreatedAt(new \DateTimeImmutable());

            // Prix avec gestion promo
            $regularPriceClean = str_replace(',', '.', $regularPrice);
            if (!empty($regularPrice) && is_numeric($regularPriceClean) && (float)$regularPriceClean > 0 && $regularPriceClean !== $prixClean) {
                // regularPrice = prix avant promo, prix = prix actuel (soldé)
                $product->setPrix($regularPriceClean);
                $product->setPrixPromo($prixClean);
                $product->setIsPromo(true);
            } else {
                $product->setPrix($prixClean);
                $product->setPrixPromo(null);
            }

            if ($category) {
                $product->setCategory($category);
            }

            $this->entityManager->persist($product);
            $existingNames[strtolower($nom)] = true;
            $imported++;
            $count++;

            // Flush par batch
            if ($count % $batchSize === 0) {
                $this->entityManager->flush();
                $io->writeln("<info>$count produits traités...</info>");
            }
        }

        $this->entityManager->flush();
        fclose($handle);

        $io->success("Import terminé ! $imported produits importés, $skipped ignorés.");
        return Command::SUCCESS;
    }
}