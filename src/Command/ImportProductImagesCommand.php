<?php

namespace App\Command;

use App\Entity\Product;
use App\Entity\ProductImage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import-product-images',
    description: 'Importe les images produits depuis les URLs du CSV SumUp',
)]
class ImportProductImagesCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private string $projectDir,
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

        $uploadsDir = $this->projectDir . '/public/uploads/products';
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);
        }

        $handle = fopen($file, 'r');
        $headers = null;
        $imported = 0;
        $skipped = 0;
        $errors = 0;
        $count = 0;

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            if ($headers === null) {
                $headers = array_map('trim', $row);
                continue;
            }

            if (count($row) < count($headers)) {
                $row = array_pad($row, count($headers), '');
            }

            $data = array_combine($headers, $row);

            $nom = trim($data['Item name'] ?? '');
            $imageUrl = trim($data['Image 1'] ?? '');

            if (empty($nom) || empty($imageUrl)) {
                $skipped++;
                continue;
            }

            // Chercher le produit en base par nom
            $product = $this->entityManager->getRepository(Product::class)
                ->createQueryBuilder('p')
                ->where('TRIM(p.nom) = :nom')
                ->setParameter('nom', trim($nom))
                ->getQuery()
                ->getOneOrNullResult();

            if (!$product) {
                $io->writeln("<comment>Produit non trouvé : $nom</comment>");
                $skipped++;
                continue;
            }

            // Vérifier si le produit a déjà une image
            if ($product->getProductImages()->count() > 0) {
                $skipped++;
                continue;
            }

            // Télécharger l'image
            try {
                $context = stream_context_create([
                    'http' => [
                        'timeout' => 30,
                        'user_agent' => 'Mozilla/5.0',
                    ],
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                    ],
                ]);

                $imageData = @file_get_contents($imageUrl, false, $context);

                if ($imageData === false || strlen($imageData) < 100) {
                    $io->writeln("<error>Téléchargement échoué : $nom</error>");
                    $errors++;
                    continue;
                }

                // Détecter le type d'image
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $mimeType = $finfo->buffer($imageData);
                $extension = match($mimeType) {
                    'image/jpeg' => 'jpg',
                    'image/png'  => 'png',
                    'image/webp' => 'webp',
                    'image/gif'  => 'gif',
                    default      => 'jpg',
                };

                $filename = 'product_' . uniqid() . '.' . $extension;
                $destPath = $uploadsDir . '/' . $filename;
                file_put_contents($destPath, $imageData);

                $productImage = new ProductImage();
                $productImage->setFilename($filename);
                $productImage->setIsMain(true);
                $productImage->setPosition(1);
                $productImage->setProduct($product);
                $productImage->setUpdatedAt(new \DateTimeImmutable());

                $this->entityManager->persist($productImage);
                $imported++;
                $count++;

                $io->writeln("<info>✅ $nom</info>");

                if ($count % 20 === 0) {
                    $this->entityManager->flush();
                    $io->writeln("<info>--- $count images traitées ---</info>");
                    sleep(1);
                }

            } catch (\Exception $e) {
                $io->writeln("<error>Erreur $nom : " . $e->getMessage() . "</error>");
                $errors++;
            }
        }

        $this->entityManager->flush();
        fclose($handle);

        $io->success("Import terminé ! $imported images importées, $skipped ignorées, $errors erreurs.");
        return Command::SUCCESS;
    }
}