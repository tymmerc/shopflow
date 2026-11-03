<?php

namespace App\Cart;

use App\Entity\Cart;
use App\Entity\CartCatalog;
use App\Entity\Catalog;
use App\Repository\CartRepository;
use App\Repository\CatalogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Panier du visiteur, retrouvé grâce au cookie `cart` tant qu'il n'y a pas de comptes utilisateurs.
 * Un cookie dédié plutôt que la session : celle-ci expire après 24 min d'inactivité.
 * Les quantités sont toujours bornées par le stock du catalogue.
 */
final class CartService
{
    private const COOKIE = 'cart';
    private const COOKIE_LIFETIME = '+30 days';

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly CartRepository $carts,
        private readonly CatalogRepository $catalogs,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /** Ne crée pas de panier : un simple affichage ne doit rien écrire en base. */
    public function current(): ?Cart
    {
        $token = $this->requestStack->getCurrentRequest()?->cookies->get(self::COOKIE);

        return \is_string($token) ? $this->carts->findOneBy(['token' => $token]) : null;
    }

    public function add(Catalog $catalog, int $quantity): Cart
    {
        $cart = $this->currentOrCreate();
        $this->apply($cart, $catalog, ($this->itemFor($cart, $catalog)?->getQuantity() ?? 0) + $quantity);
        $this->entityManager->flush();

        return $cart;
    }

    public function setQuantity(Catalog $catalog, int $quantity): Cart
    {
        $cart = $this->currentOrCreate();
        $this->apply($cart, $catalog, $quantity);
        $this->entityManager->flush();

        return $cart;
    }

    public function remove(Catalog $catalog): ?Cart
    {
        $cart = $this->current();
        if (null !== $cart) {
            $this->apply($cart, $catalog, 0);
            $this->entityManager->flush();
        }

        return $cart;
    }

    /**
     * Remplace tout le contenu du panier. Les articles disparus du catalogue sont ignorés.
     *
     * @param array<int, int> $quantities quantité par id d'article
     */
    public function replace(array $quantities): Cart
    {
        $cart = $this->currentOrCreate();

        // On met à jour les lignes existantes au lieu de tout supprimer puis recréer :
        // Doctrine exécute les INSERT avant les DELETE, ce qui violerait la contrainte unique cart/catalog
        foreach ($cart->getItems()->toArray() as $item) {
            if (!isset($quantities[(int) $item->getCatalog()?->getId()])) {
                $cart->removeItem($item);
            }
        }
        foreach ($this->catalogs->findBy(['id' => array_keys($quantities)]) as $catalog) {
            $this->apply($cart, $catalog, $quantities[$catalog->getId()]);
        }
        $this->entityManager->flush();

        return $cart;
    }

    public function clear(): ?Cart
    {
        $cart = $this->current();
        if (null !== $cart) {
            foreach ($cart->getItems()->toArray() as $item) {
                $cart->removeItem($item);
            }
            $this->entityManager->flush();
        }

        return $cart;
    }

    /** À renvoyer à chaque réponse : la durée de vie repart de zéro à chaque visite. */
    public function cookie(Cart $cart): Cookie
    {
        return Cookie::create(self::COOKIE, $cart->getToken(), new \DateTimeImmutable(self::COOKIE_LIFETIME));
    }

    private function currentOrCreate(): Cart
    {
        $cart = $this->current();
        if (null === $cart) {
            $cart = new Cart();
            $this->entityManager->persist($cart);
        }

        return $cart;
    }

    /** Quantité bornée par le stock ; 0 ou moins retire l'article. */
    private function apply(Cart $cart, Catalog $catalog, int $quantity): void
    {
        $quantity = min($quantity, $catalog->getStock() ?? 0);
        $item = $this->itemFor($cart, $catalog);

        if ($quantity <= 0) {
            if (null !== $item) {
                $cart->removeItem($item);
            }

            return;
        }

        if (null === $item) {
            $item = (new CartCatalog())->setCatalog($catalog);
            $cart->addItem($item);
        }
        $item->setQuantity($quantity);
    }

    private function itemFor(Cart $cart, Catalog $catalog): ?CartCatalog
    {
        return $cart->getItems()->findFirst(static fn (int $key, CartCatalog $item): bool => $item->getCatalog() === $catalog);
    }
}
