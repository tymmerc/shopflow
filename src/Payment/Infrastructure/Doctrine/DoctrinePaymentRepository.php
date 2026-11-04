<?php

namespace App\Payment\Infrastructure\Doctrine;

use App\Entity\Payment as PaymentEntity;
use App\Payment\Application\Port\PaymentRepository;
use App\Payment\Domain\Payment;
use App\Repository\PaymentRepository as PaymentEntityRepository;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrinePaymentRepository implements PaymentRepository
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PaymentEntityRepository $repository,
    ) {
    }

    public function save(Payment $payment): void
    {
        $reference = $payment->getProviderReference()
            ?? throw new \LogicException('Un paiement doit avoir sa référence PSP avant d\'être enregistré.');

        $entity = (null !== $payment->getId() ? $this->repository->find($payment->getId()) : null) ?? new PaymentEntity();
        $entity
            ->setOrderId($payment->getOrderId())
            ->setAmount($payment->getAmount())
            ->setStatus($payment->getStatus())
            ->setProviderReference($reference);

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        if (null === $payment->getId()) {
            $payment->assignId($entity->getId());
        }
    }

    public function findByProviderReference(string $providerReference): ?Payment
    {
        $entity = $this->repository->findOneBy(['providerReference' => $providerReference]);

        return null !== $entity ? $this->toDomain($entity) : null;
    }

    private function toDomain(PaymentEntity $entity): Payment
    {
        return Payment::reconstitute(
            $entity->getId(),
            $entity->getOrderId(),
            $entity->getAmount(),
            $entity->getStatus(),
            $entity->getProviderReference(),
        );
    }
}
