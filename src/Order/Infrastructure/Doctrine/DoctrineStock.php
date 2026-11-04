<?php

namespace App\Order\Infrastructure\Doctrine;

use App\Entity\Catalog;
use App\Order\Application\Port\Stock;
use App\Order\Domain\Exception\InsufficientStockException;
use App\Repository\CatalogRepository;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineStock implements Stock
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CatalogRepository $catalogs,
    ) {
    }

    public function availableQuantity(int $catalogId): int
    {
        return $this->catalogs->find($catalogId)?->getStock() ?? 0;
    }

    public function decrement(array $quantities): void
    {
        // Transaction de la connexion, pas wrapInTransaction() : celle-ci fermerait l'EntityManager
        // en cas d'exception, et plus rien ne pourrait être enregistré dans la requête
        $this->entityManager->getConnection()->transactional(function () use ($quantities): void {
            foreach ($quantities as $catalogId => $quantity) {
                // Une seule requête qui vérifie et décrémente : deux clients qui paient le dernier
                // article en même temps ne peuvent pas faire passer le stock sous zéro
                $updated = $this->entityManager->createQuery(
                    'UPDATE '.Catalog::class.' c SET c.stock = c.stock - :quantity WHERE c.id = :id AND c.stock >= :quantity'
                )
                    ->setParameter('quantity', $quantity)
                    ->setParameter('id', $catalogId)
                    ->execute();

                // L'exception annule la transaction : les articles déjà décrémentés sont remis
                if (0 === $updated) {
                    throw new InsufficientStockException($catalogId);
                }
            }
        });

        // La requête UPDATE contourne les entités chargées : on évite qu'elles gardent l'ancien stock
        foreach (array_keys($quantities) as $catalogId) {
            $catalog = $this->entityManager->getUnitOfWork()->tryGetById($catalogId, Catalog::class);
            if ($catalog instanceof Catalog) {
                $this->entityManager->refresh($catalog);
            }
        }
    }
}
