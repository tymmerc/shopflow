<?php

namespace App\Order\UI\Http;

use App\Order\Application\PlaceOrderHandler;
use App\Order\Application\Port\OrderRepository;
use App\Order\Domain\Exception\EmptyCartException;
use App\Order\Domain\Exception\InsufficientStockException;
use App\Repository\CatalogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;

final class OrderController extends AbstractController
{
    // TODO: remplacer par l'utilisateur connecté ($this->getUser()) quand l'authentification existera
    private const TEMPORARY_USER_ID = 1;

    #[Route('/order', name: 'app_order_place', methods: ['POST'])]
    #[IsCsrfTokenValid('order')]
    public function place(PlaceOrderHandler $placeOrder, CatalogRepository $catalogs): Response
    {
        try {
            $placed = $placeOrder(self::TEMPORARY_USER_ID);
        } catch (EmptyCartException $e) {
            $this->addFlash('error', $e->getMessage());

            return $this->redirectToRoute('app_cart');
        } catch (InsufficientStockException $e) {
            $catalog = $catalogs->find($e->getCatalogId());
            $name = $catalog?->getName() ?? 'Un article';
            $left = $catalog?->getStock() ?? 0;
            $this->addFlash('error', $left > 0
                ? \sprintf('Il ne reste que %d « %s » en stock. Ajustez la quantité dans votre panier.', $left, $name)
                : \sprintf('« %s » n\'est plus en stock. Retirez-le de votre panier pour commander.', $name));

            return $this->redirectToRoute('app_cart');
        }

        // Vers la page du PSP (le faux en dev) ; 303 pour que le navigateur la charge en GET
        return $this->redirect($placed->getPaymentUrl(), Response::HTTP_SEE_OTHER);
    }

    // Page où le PSP renvoie le client après le paiement
    #[Route('/orders/{id<\d+>}', name: 'app_order_show', methods: ['GET'])]
    public function show(int $id, OrderRepository $orders, CatalogRepository $catalogs): Response
    {
        $order = $orders->get($id);
        if (null === $order || self::TEMPORARY_USER_ID !== $order->getUserId()) {
            throw $this->createNotFoundException('Commande introuvable.');
        }

        $articles = [];
        foreach ($catalogs->findBy(['id' => array_map(static fn ($line) => $line->getCatalogId(), $order->getLines())]) as $catalog) {
            $articles[$catalog->getId()] = $catalog;
        }

        return $this->render('order/show.html.twig', [
            'order' => $order,
            'articles' => $articles,
        ]);
    }

    #[Route('/orders', name: 'app_orders', methods: ['GET'])]
    public function history(OrderRepository $orders): Response
    {
        // TODO: créer templates/order/index.html.twig (date, statut, total, lien vers le détail)
        return $this->render('order/index.html.twig', [
            'orders' => $orders->findByUser(self::TEMPORARY_USER_ID),
        ]);
    }
}
