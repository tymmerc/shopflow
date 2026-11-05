<?php

namespace App\Controller;

use App\Cart\CartService;
use App\Entity\Cart;
use App\Entity\CartCatalog;
use App\Entity\Catalog;
use App\Repository\CatalogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Symfony\Component\Routing\Attribute\Route;

/**
 * API JSON du panier, appelée par assets/lib/cart.js (contrat : docs/api/openapi.yaml, tag Panier).
 * Chaque réponse renvoie le panier complet.
 */
#[Route('/api/v1/cart', name: 'api_cart_', format: 'json')]
final class CartApiController extends AbstractController
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly CatalogRepository $catalogs,
    ) {
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
        $items = $this->payload($request)['items'] ?? null;
        // Sans ce contrôle, un corps sans « items » viderait le panier en silence
        if (!\is_array($items) || !array_is_list($items)) {
            throw new UnprocessableEntityHttpException('Le champ items doit être une liste de lignes.');
        }

        $quantities = [];
        foreach ($items as $line) {
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
    public function add(int $id, Request $request): JsonResponse
    {
        $catalog = $this->product($id);
        $quantity = $this->quantity($this->payload($request)['quantity'] ?? null);

        return $this->respond($this->cartService->add($catalog, $quantity));
    }

    #[Route('/items/{id<\d+>}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $catalog = $this->product($id);
        $quantity = $this->quantity($this->payload($request)['quantity'] ?? null);

        return $this->respond($this->cartService->setQuantity($catalog, $quantity));
    }

    #[Route('/items/{id<\d+>}', name: 'remove', methods: ['DELETE'])]
    public function remove(int $id): JsonResponse
    {
        return $this->respond($this->cartService->remove($this->product($id)));
    }

    // Recherche à la main plutôt que par l'argument Catalog : on garde un message d'erreur en français
    private function product(int $id): Catalog
    {
        return $this->catalogs->find($id) ?? throw new NotFoundHttpException('Produit introuvable.');
    }

    /**
     * Exiger du JSON protège du CSRF : un formulaire d'un autre site ne peut pas envoyer ce
     * Content-Type, et fetch() cross-origin serait bloqué par le preflight CORS.
     * Un JSON illisible est aussi refusé en 415 : le contrat ne prévoit pas de 400 pour le panier.
     * Tableau brut plutôt qu'InputBag : InputBag::get() répond 400 si la valeur n'est pas scalaire.
     *
     * @return array<mixed>
     */
    private function payload(Request $request): array
    {
        if ('json' !== $request->getContentTypeFormat()) {
            throw new UnsupportedMediaTypeHttpException('Le corps de la requête doit être en JSON.');
        }

        try {
            return $request->getPayload()->all();
        } catch (JsonException $e) {
            throw new UnsupportedMediaTypeHttpException('Le corps de la requête n\'est pas du JSON valide.', $e);
        }
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
