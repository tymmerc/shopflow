<?php

namespace App\Controller;

use App\Entity\Catalog;
use App\Repository\CatalogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

/**
 * API des produits, en lecture seule (contrat : docs/api/openapi.yaml, tag Produits).
 * format: 'json' fait renvoyer les erreurs au format Problem (RFC 7807) au lieu d'une page HTML.
 */
#[Route('/api/v1/products', name: 'api_products_', format: 'json')]
final class ProductApiController extends AbstractController
{
    private const LEVELS = ['Débutant', 'Intermédiaire', 'Confirmé'];

    public function __construct(private readonly CatalogRepository $catalogs)
    {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $category = $request->query->getString('category');
        $level = $request->query->getString('level');

        // Les catégories viennent de la base : une catégorie ajoutée demain sera acceptée sans toucher au code
        if ('' !== $category && !\array_key_exists($category, $this->catalogs->countByCategory())) {
            throw new BadRequestHttpException(\sprintf('Catégorie inconnue « %s ».', $category));
        }
        if ('' !== $level && !\in_array($level, self::LEVELS, true)) {
            throw new BadRequestHttpException(\sprintf('Niveau inconnu « %s ». Valeurs possibles : %s.', $level, implode(', ', self::LEVELS)));
        }

        $criteria = array_filter(['category' => $category, 'level' => $level], static fn (string $value) => '' !== $value);
        $products = $this->catalogs->findBy($criteria, ['id' => 'ASC']);
        // Les ruptures restent dans la liste, mais à la fin (comme sur le site)
        usort($products, static fn (Catalog $a, Catalog $b) => ($a->getStock() <= 0) <=> ($b->getStock() <= 0));

        return $this->json(['items' => array_map($this->summary(...), $products)]);
    }

    #[Route('/{id<\d+>}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        // Recherche à la main plutôt que par l'argument Catalog : on garde un message d'erreur en français
        $product = $this->catalogs->find($id) ?? throw new NotFoundHttpException('Produit introuvable.');

        return $this->json([
            ...$this->summary($product),
            'tagline' => $product->getTagline(),
            'description' => $product->getDesc(),
            'solderingRequired' => $product->isSolderingRequired(),
            'specs' => $product->getSpecs() ?? [],
            'compatibility' => $product->getCompatibility() ?? [],
            'boxContents' => $product->getBoxContents() ?? [],
        ]);
    }

    /**
     * @return array{id: int|null, name: string|null, category: string|null, level: string|null, price: float, stock: int|null, image: string|null}
     */
    private function summary(Catalog $product): array
    {
        return [
            'id' => $product->getId(),
            'name' => $product->getName(),
            'category' => $product->getCategory(),
            'level' => $product->getLevel(),
            'price' => (float) $product->getPrice(),
            'stock' => $product->getStock(),
            'image' => $product->getImg(),
        ];
    }
}
