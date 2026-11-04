<?php

namespace App\Notification\Infrastructure;

use App\Notification\Application\Port\OrderConfirmationSender;
use App\Order\Domain\Order;
use App\Repository\CatalogRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class MailerOrderConfirmationSender implements OrderConfirmationSender
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly CatalogRepository $catalogs,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function send(Order $order, string $recipient): void
    {
        $names = [];
        foreach ($this->catalogs->findBy(['id' => array_map(static fn ($line) => $line->getCatalogId(), $order->getLines())]) as $catalog) {
            $names[$catalog->getId()] = $catalog->getName();
        }

        // Le contexte ne contient que des valeurs simples : l'e-mail est sérialisé pour partir en file (Messenger)
        $lines = [];
        foreach ($order->getLines() as $line) {
            $lines[] = [
                'name' => $names[$line->getCatalogId()] ?? 'Article '.$line->getCatalogId(),
                'quantity' => $line->getQuantity(),
                'unitPrice' => self::euros($line->getUnitPriceCents()),
                'subtotal' => self::euros($line->subtotalCents()),
            ];
        }

        $this->mailer->send((new TemplatedEmail())
            ->to($recipient)
            ->subject(\sprintf('Votre commande n° %d est confirmée', $order->getId()))
            ->htmlTemplate('email/order_confirmation.html.twig')
            ->context([
                'orderId' => $order->getId(),
                'placedAt' => $order->getPlacedAt(),
                'lines' => $lines,
                'total' => self::euros($order->totalCents()),
                'orderUrl' => $this->urlGenerator->generate('app_order_show', ['id' => $order->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
            ]));
    }

    // Toujours un float : en PHP, 2500 / 100 donne l'entier 25
    private static function euros(int $cents): float
    {
        return $cents / 100;
    }
}
