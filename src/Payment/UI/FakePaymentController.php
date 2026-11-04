<?php

namespace App\Payment\UI;

use App\Payment\Application\ConfirmPaymentHandler;
use App\Payment\Application\Exception\PaymentNotFoundException;
use App\Payment\Application\Port\PaymentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Page qui imite celle d'un PSP (FakePaymentGateway y envoie le client). Le futur webhook
 * du vrai PSP sera un autre adaptateur primaire à côté, qui appellera le même ConfirmPaymentHandler.
 * Pas de jeton CSRF : cette page n'existe jamais en production.
 */
final class FakePaymentController extends AbstractController
{
    public function __construct(
        private readonly PaymentRepository $payments,
        #[Autowire('%kernel.environment%')]
        private readonly string $environment,
    ) {
    }

    // Nom de route utilisé par FakePaymentGateway::ROUTE
    #[Route('/payment/fake/{reference}', name: 'payment_fake_show', methods: ['GET'])]
    public function show(string $reference, Request $request): Response
    {
        $this->denyInProduction();

        return $this->render('payment/fake.html.twig', [
            'payment' => $this->payments->findByProviderReference($reference)
                ?? throw $this->createNotFoundException('Paiement inconnu.'),
            'return' => $this->localUrl($request->query->getString('return'), $request),
        ]);
    }

    #[Route('/payment/fake/{reference}', name: 'payment_fake_confirm', methods: ['POST'])]
    public function confirm(string $reference, Request $request, ConfirmPaymentHandler $confirmPayment): Response
    {
        $this->denyInProduction();

        $decision = $request->request->getString('decision');
        if (!\in_array($decision, ['pay', 'refuse'], true)) {
            throw new BadRequestHttpException('Décision attendue : pay ou refuse.');
        }

        try {
            $confirmPayment($reference, 'pay' === $decision);
        } catch (PaymentNotFoundException $e) {
            throw $this->createNotFoundException($e->getMessage(), $e);
        }

        return $this->redirect($this->localUrl($request->request->getString('return'), $request), Response::HTTP_SEE_OTHER);
    }

    private function denyInProduction(): void
    {
        // Sinon n'importe qui pourrait se déclarer « payé » en production
        if ('prod' === $this->environment) {
            throw $this->createNotFoundException();
        }
    }

    // `return` vient de l'URL : on ne redirige que vers notre propre site, sinon vers l'accueil
    private function localUrl(string $url, Request $request): string
    {
        $isPath = str_starts_with($url, '/') && !str_starts_with($url, '//') && !str_contains($url, '\\');
        // URL absolue acceptée seulement vers notre domaine (c'est ce qu'envoie OrderConfirmationUrl)
        $isOurSite = str_starts_with($url, $request->getSchemeAndHttpHost().'/');

        return $isPath || $isOurSite ? $url : $this->generateUrl('app_home');
    }
}
