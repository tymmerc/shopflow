<?php

namespace App\Order\Infrastructure\Doctrine;

use App\Entity\Catalog;
use App\Entity\Order as OrderEntity;
use App\Entity\OrderCatalog;
use App\Order\Application\Port\OrderRepository;
use App\Order\Domain\OrderLine;
use App\Order\Domain\Order;
use App\Repository\OrderRepository as OrderEntityRepository;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineOrderRepository implements OrderRepository
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly OrderEntityRepository $repository,
    ) {
    }

    public function save(Order $order): void
    {
        $entity = (null !== $order->getId() ? $this->repository->find($order->getId()) : null) ?? new OrderEntity();
        $entity->setUserId($order->getUserId());

        if ($entity->getItems()->isEmpty()) {
            foreach ($order->getLines() as $line) {
                $entity->addItem(
                    (new OrderCatalog())
                        ->setCatalog($this->entityManager->getReference(Catalog::class, $line->getCatalogId()))
                        ->setQuantity($line->getQuantity())
                        ->setUnitPrice(number_format($line->getUnitPriceCents() / 100, 2, '.', ''))
                );
            }
        }

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        if (null === $order->getId()) {
            $order->assignId($entity->getId());
        }
    }

    public function get(int $id): ?Order
    {
        $entity = $this->repository->find($id);

        return null !== $entity ? $this->toDomain($entity) : null;
    }

    public function findByUser(int $userId): array
    {
        return array_map(
            fn (OrderEntity $entity): Order => $this->toDomain($entity),
            $this->repository->findBy(['userId' => $userId], ['id' => 'DESC']),
        );
    }

    private function toDomain(OrderEntity $entity): Order
    {
        $lines = [];
        foreach ($entity->getItems() as $item) {
            $lines[] = new OrderLine(
                $item->getCatalog()->getId(),
                (int) round((float) $item->getUnitPrice() * 100),
                $item->getQuantity(),
            );
        }

        return Order::reconstitute($entity->getId(), $entity->getUserId(), $lines);
    }
}
