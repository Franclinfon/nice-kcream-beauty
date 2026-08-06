<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function findFiltered(
        ?string $categorySlug = null,
        ?string $search = null,
        ?string $sort = null,
        ?bool $nouveautesOnly = false,
        ?bool $promosOnly = false,
    ): array {
        $qb = $this->createQueryBuilder('p')
            ->join('p.category', 'c')
            ->where('p.isActive = true');

        if ($categorySlug) {
            $qb->andWhere('c.slug = :categorySlug')
               ->setParameter('categorySlug', $categorySlug);
        }

        if ($search) {
            $qb->andWhere('p.nom LIKE :search OR p.description LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        if ($nouveautesOnly) {
            $qb->andWhere('p.isNouveaute = true');
        }

        if ($promosOnly) {
            $qb->andWhere('p.isPromo = true');
        }

        match ($sort) {
            'prix_asc' => $qb->orderBy('p.prix', 'ASC'),
            'prix_desc' => $qb->orderBy('p.prix', 'DESC'),
            'nouveautes' => $qb->orderBy('p.createdAt', 'DESC'),
            default => $qb->orderBy('p.createdAt', 'DESC'),
        };

        return $qb->getQuery()->getResult();
    }
}