<?php

namespace App\Tests\Functional;

use App\Entity\Catalog;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Parcours complet, comme dans le navigateur : panier → « Passer commande » → faux PSP → page de commande.
 * Base de test : shopflow_test (voir README, section Tests).
 */
final class CheckoutTest extends WebTestCase
{
    private KernelBrowser $client;
    private int $mugId;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        // Chaque test repart d'une base vide
        $db = $this->db();
        $db->executeStatement('SET FOREIGN_KEY_CHECKS = 0');
        foreach (['order_catalog', '`order`', 'payment', 'cart_catalog', 'cart', 'catalog'] as $table) {
            $db->executeStatement("TRUNCATE TABLE $table");
        }
        $db->executeStatement('SET FOREIGN_KEY_CHECKS = 1');

        $mug = (new Catalog())->setName('Mug céramique')->setPrice('12.50')->setStock(10);
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->persist($mug);
        $entityManager->flush();
        $this->mugId = (int) $mug->getId();
    }

    public function testPayingConfirmsTheOrderDecrementsStockClearsTheCartAndSendsAnEmail(): void
    {
        $this->addMugsToCart(2);
        $this->placeOrderAndReachThePaymentPage();

        $this->choose('Payer');

        // Le mail part pendant la confirmation du paiement : à vérifier avant de suivre la redirection
        $this->assertQueuedEmailCount(1);
        $email = $this->getMailerMessage();
        $this->assertNotNull($email);
        $this->assertEmailAddressContains($email, 'To', 'client@shopflow.test');
        $this->assertEmailHeaderSame($email, 'Subject', \sprintf('Votre commande n° %d est confirmée', $this->orderId()));
        $this->assertEmailHtmlBodyContains($email, 'Mug céramique');
        $this->assertEmailHtmlBodyContains($email, '25,00 €');

        $this->assertResponseRedirects(\sprintf('http://localhost/orders/%d', $this->orderId()), 303);
        $this->client->followRedirect();
        $this->assertSelectorTextContains('h1', 'Merci, votre commande est confirmée');

        $this->assertSame('paid', $this->orderStatus());
        $this->assertSame('succeeded', $this->db()->fetchOne('SELECT status FROM payment'));
        $this->assertSame(8, $this->stock());
        $this->assertSame([], $this->cartItems());
    }

    public function testRefusingThePaymentKeepsTheCartAndTheStock(): void
    {
        $this->addMugsToCart(2);
        $this->placeOrderAndReachThePaymentPage();

        $this->choose('Refuser le paiement');

        $this->assertQueuedEmailCount(0);
        $this->client->followRedirect();
        $this->assertSelectorTextContains('h1', 'Le paiement a été refusé');

        $this->assertSame('payment_failed', $this->orderStatus());
        $this->assertSame('failed', $this->db()->fetchOne('SELECT status FROM payment'));
        $this->assertSame(10, $this->stock());
        $this->assertSame(2, $this->cartItems()[0]['qty'] ?? null);
    }

    public function testConfirmingTheSamePaymentTwiceChangesNothing(): void
    {
        $this->addMugsToCart(2);
        $crawler = $this->placeOrderAndReachThePaymentPage();
        $form = $crawler->selectButton('Payer')->form();

        $this->client->submit($form);
        // Double-clic, ou webhook renvoyé par le PSP
        $this->client->submit($form);

        $this->assertQueuedEmailCount(0);
        $this->assertSame('paid', $this->orderStatus());
        $this->assertSame(8, $this->stock());

        $this->client->request('GET', $crawler->getUri());
        $this->assertSelectorTextContains('section', 'Ce paiement a déjà été traité');
    }

    public function testOrderingMoreThanTheStockLeftIsRefused(): void
    {
        $this->addMugsToCart(2);
        // Le stock baisse entre l'ajout au panier et la commande
        $this->db()->executeStatement('UPDATE catalog SET stock = 1 WHERE id = ?', [$this->mugId]);

        $this->clickPlaceOrder();

        $this->assertResponseRedirects('/cart');
        $this->client->followRedirect();
        $this->assertSelectorExists('[data-controller="flash"][data-flash-message-value*="Il ne reste que 1 « Mug céramique »"]');
        $this->assertSame(0, (int) $this->db()->fetchOne('SELECT COUNT(*) FROM `order`'));
        $this->assertSame(0, (int) $this->db()->fetchOne('SELECT COUNT(*) FROM payment'));
    }

    public function testAnEmptyCartCannotBeOrdered(): void
    {
        $this->clickPlaceOrder();

        $this->assertResponseRedirects('/cart');
        $this->client->followRedirect();
        $this->assertSelectorExists('[data-controller="flash"][data-flash-message-value*="le panier est vide"]');
        $this->assertSame(0, (int) $this->db()->fetchOne('SELECT COUNT(*) FROM `order`'));
    }

    private function addMugsToCart(int $quantity): void
    {
        $this->client->jsonRequest('POST', "/api/v1/cart/items/{$this->mugId}", ['quantity' => $quantity]);
        $this->assertResponseIsSuccessful();
    }

    // Formulaire « Passer commande » de la page panier (jeton CSRF compris)
    private function clickPlaceOrder(): void
    {
        $crawler = $this->client->request('GET', '/cart');
        $this->client->submit($crawler->selectButton('Passer commande')->form());
    }

    private function placeOrderAndReachThePaymentPage(): \Symfony\Component\DomCrawler\Crawler
    {
        $this->clickPlaceOrder();
        $this->assertResponseStatusCodeSame(303);
        $this->assertStringContainsString('/payment/fake/fake_', (string) $this->client->getResponse()->headers->get('Location'));

        $crawler = $this->client->followRedirect();
        $this->assertSelectorTextContains('h1', 'Faux PSP');
        $this->assertSelectorTextContains('dd', '25,00 €');

        return $crawler;
    }

    private function choose(string $button): void
    {
        $this->client->submit($this->client->getCrawler()->selectButton($button)->form());
        $this->assertResponseStatusCodeSame(303);
    }

    private function db(): Connection
    {
        return static::getContainer()->get(Connection::class);
    }

    private function orderId(): int
    {
        return (int) $this->db()->fetchOne('SELECT id FROM `order`');
    }

    private function orderStatus(): string|false
    {
        return $this->db()->fetchOne('SELECT status FROM `order`');
    }

    private function stock(): int
    {
        return (int) $this->db()->fetchOne('SELECT stock FROM catalog WHERE id = ?', [$this->mugId]);
    }

    /**
     * @return list<array{id: int, qty: int}>
     */
    private function cartItems(): array
    {
        $this->client->request('GET', '/api/v1/cart');

        return json_decode((string) $this->client->getResponse()->getContent(), true)['items'];
    }
}
