<?php

namespace App\Repository;

use App\Entity\Catalog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Catalog>
 */
class CatalogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Catalog::class);
    }

    /**
     * @return Catalog[]
     */
    public function findAll(): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Derniers articles ajoutés, pour la page d'accueil.
     *
     * @return Catalog[]
     */
    public function findLatest(int $limit): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Autres produits de la même catégorie, ceux en stock d'abord.
     *
     * @return Catalog[]
     */
    public function findRelated(Catalog $product, int $limit): array
    {
        if ($product->getCategory() === null) {
            return [];
        }

        return $this->createQueryBuilder('c')
            ->andWhere('c.category = :category')
            ->andWhere('c.id != :id')
            ->setParameter('category', $product->getCategory())
            ->setParameter('id', $product->getId())
            ->addSelect('CASE WHEN c.stock > 0 THEN 0 ELSE 1 END AS HIDDEN outOfStock')
            ->addOrderBy('outOfStock', 'ASC')
            ->addOrderBy('c.id', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Les produits « à prévoir aussi » d'une fiche, dans l'ordre choisi, sans ceux en rupture.
     *
     * @return Catalog[]
     */
    public function findAccessories(Catalog $product): array
    {
        $names = $product->getAccessories() ?? [];
        if ($names === []) {
            return [];
        }

        $found = $this->findBy(['name' => $names]);
        $byName = [];
        foreach ($found as $item) {
            $byName[$item->getName()] = $item;
        }

        return array_values(array_filter(
            array_map(static fn (string $name) => $byName[$name] ?? null, $names),
            static fn (?Catalog $item) => $item !== null && $item->getStock() > 0,
        ));
    }

    /**
     * Nombre de produits par catégorie, dans l'ordre alphabétique.
     *
     * @return array<string, int>
     */
    public function countByCategory(): array
    {
        $rows = $this->createQueryBuilder('c')
            ->select('c.category AS category, COUNT(c.id) AS total')
            ->andWhere('c.category IS NOT NULL')
            ->groupBy('c.category')
            ->orderBy('c.category', 'ASC')
            ->getQuery()
            ->getArrayResult();

        return array_column($rows, 'total', 'category');
    }

    //    /**
    //     * @return Catalog[] Returns an array of Catalog objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Catalog
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
