<?php

namespace App\Order\UI\Http;

use App\Order\Application\PlaceOrderHandler;
use App\Order\Application\Port\OrderRepository;
use App\Order\Domain\Exception\EmptyCartException;
use App\Order\Domain\Exception\InsufficientStockException;
use App\Order\Domain\Order;
use App\Repository\CatalogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Symfony\Component\Routing\Attribute\Route;

/**
 * API des commandes (contrat : docs/api/openapi.yaml, tag Commandes).
 * Adaptateur primaire de l'hexagone Order : il appelle les mêmes cas d'usage que OrderController (HTML),
 * seule la forme de la réponse change (JSON au lieu d'une redirection).
 */
#[Route('/api/orders', name: 'api_orders_', format: 'json')]
final class OrderApiController extends AbstractController
{
    // TODO: remplacer par l'utilisateur connecté quand l'authentification existera (comme OrderController)
    private const TEMPORARY_USER_ID = 1;

    public function __construct(
        private readonly OrderRepository $orders,
        private readonly CatalogRepository $catalogs,
    ) {
    }

    #[Route('', name: 'place', methods: ['POST'])]
    public function place(Request $request, PlaceOrderHandler $placeOrder): JsonResponse
    {
        // Comme pour le panier : exiger du JSON protège du CSRF (un formulaire d'un autre site ne peut pas en envoyer)
        if ('json' !== $request->getContentTypeFormat()) {
            throw new UnsupportedMediaTypeHttpException('Le corps de la requête doit être en JSON (un objet vide {} suffit).');
        }

        try {
            $placed = $placeOrder(self::TEMPORARY_USER_ID);
        } catch (EmptyCartException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        } catch (InsufficientStockException $e) {
            $name = $this->catalogs->find($e->getCatalogId())?->getName() ?? 'Un article';
            throw new ConflictHttpException(\sprintf('Stock insuffisant pour « %s ». Ajustez la quantité dans le panier.', $name), $e);
        }

        $order = $placed->getOrder();
        $response = $this->json(
            [...$this->describe($order), 'paymentUrl' => $placed->getPaymentUrl()],
            Response::HTTP_CREATED,
        );
        $response->headers->set('Location', $this->generateUrl('api_orders_show', ['id' => $order->getId()]));

        return $response;
    }

    #[Route('/{id<\d+>}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $order = $this->orders->get($id);
        // Même réponse si la commande existe mais appartient à quelqu'un d'autre : on ne révèle pas son existence
        if (null === $order || self::TEMPORARY_USER_ID !== $order->getUserId()) {
            throw new NotFoundHttpException('Commande introuvable.');
        }

        return $this->json($this->describe($order));
    }

    /**
     * @return array<string, mixed>
     */
    private function describe(Order $order): array
    {
        $names = [];
        foreach ($this->catalogs->findBy(['id' => array_map(static fn ($line) => $line->getCatalogId(), $order->getLines())]) as $catalog) {
            $names[$catalog->getId()] = $catalog->getName();
        }

        return [
            'id' => $order->getId(),
            'status' => $order->getStatus()->value,
            'placedAt' => $order->getPlacedAt()->format(\DATE_ATOM),
            'total' => $order->totalCents() / 100,
            'lines' => array_map(static fn ($line) => [
                'productId' => $line->getCatalogId(),
                'name' => $names[$line->getCatalogId()] ?? null,
                'unitPrice' => $line->getUnitPriceCents() / 100,
                'quantity' => $line->getQuantity(),
                'subtotal' => $line->subtotalCents() / 100,
            ], $order->getLines()),
        ];
    }
}
