<?php

namespace App\Order\UI\Http;

use App\Order\Application\PlaceOrderHandler;
use App\Order\Application\Port\OrderRepository;
use App\Order\Domain\Exception\EmptyCartException;
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
    public function place(PlaceOrderHandler $placeOrder): Response
    {
        try {
            $placeOrder(self::TEMPORARY_USER_ID);
        } catch (EmptyCartException $e) {
            $this->addFlash('error', $e->getMessage());

            return $this->redirectToRoute('app_cart');
        }

        // TODO: rediriger vers une page de confirmation (route GET /orders/{id} + template à créer)
        return $this->redirectToRoute('app_orders');
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
