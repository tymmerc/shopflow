<?php

namespace App\Controller;

use App\Cart\CartService;
use App\Entity\Cart;
use App\Entity\CartCatalog;
use App\Entity\Catalog;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Symfony\Component\Routing\Attribute\Route;

/**
 * API JSON du panier, appelée par assets/lib/cart.js. Chaque réponse renvoie le panier complet.
 */
#[Route('/api/v1/cart', name: 'api_cart_', format: 'json')]
final class CartApiController extends AbstractController
{
    public function __construct(private readonly CartService $cartService)
    {
    }

    #[Route('', name: 'show', methods: ['GET'])]
    public function show(): JsonResponse
    {
        return $this->respond($this->cartService->current());
    }

    // Remplace tout le contenu : sert à annuler « Vider le panier »
    #[Route('', name: 'replace', methods: ['PUT'])]
    public function replace(Request $request): JsonResponse
    {
        $quantities = [];
        foreach ($this->payload($request)->all('items') as $line) {
            if (!\is_array($line) || !\is_int($line['id'] ?? null)) {
                throw new UnprocessableEntityHttpException('Chaque ligne doit avoir un id entier.');
            }
            $quantities[$line['id']] = $this->quantity($line['quantity'] ?? null);
        }

        return $this->respond($this->cartService->replace($quantities));
    }

    #[Route('', name: 'clear', methods: ['DELETE'])]
    public function clear(): JsonResponse
    {
        return $this->respond($this->cartService->clear());
    }

    // Ajout relatif (« + n ») : reste juste même si le front n'a pas encore chargé le panier
    #[Route('/items/{id<\d+>}', name: 'add', methods: ['POST'])]
    public function add(Catalog $catalog, Request $request): JsonResponse
    {
        $quantity = $this->quantity($this->payload($request)->get('quantity'));

        return $this->respond($this->cartService->add($catalog, $quantity));
    }

    #[Route('/items/{id<\d+>}', name: 'update', methods: ['PUT'])]
    public function update(Catalog $catalog, Request $request): JsonResponse
    {
        $quantity = $this->quantity($this->payload($request)->get('quantity'));

        return $this->respond($this->cartService->setQuantity($catalog, $quantity));
    }

    #[Route('/items/{id<\d+>}', name: 'remove', methods: ['DELETE'])]
    public function remove(Catalog $catalog): JsonResponse
    {
        return $this->respond($this->cartService->remove($catalog));
    }

    /**
     * Exiger du JSON protège du CSRF : un formulaire d'un autre site ne peut pas envoyer ce
     * Content-Type, et fetch() cross-origin serait bloqué par le preflight CORS.
     *
     * @return InputBag<bool|float|int|string>
     */
    private function payload(Request $request): InputBag
    {
        if ('json' !== $request->getContentTypeFormat()) {
            throw new UnsupportedMediaTypeHttpException('Le corps de la requête doit être en JSON.');
        }

        return $request->getPayload();
    }

    private function quantity(mixed $value): int
    {
        if (!\is_int($value) || $value < 1) {
            throw new UnprocessableEntityHttpException('La quantité doit être un entier supérieur ou égal à 1.');
        }

        return $value;
    }

    private function respond(?Cart $cart): JsonResponse
    {
        $response = $this->json([
            'items' => array_map($this->line(...), $cart?->getItems()->getValues() ?? []),
        ]);
        if (null !== $cart) {
            $response->headers->setCookie($this->cartService->cookie($cart));
        }

        return $response;
    }

    /**
     * @return array{id: int|null, name: string|null, price: float, stock: int|null, url: string, qty: int|null}
     */
    private function line(CartCatalog $item): array
    {
        $catalog = $item->getCatalog();

        return [
            'id' => $catalog?->getId(),
            'name' => $catalog?->getName(),
            'price' => (float) $catalog?->getPrice(),
            'stock' => $catalog?->getStock(),
            'url' => $this->generateUrl('app_catalog_show', ['id' => $catalog?->getId()]),
            'qty' => $item->getQuantity(),
        ];
    }
}
