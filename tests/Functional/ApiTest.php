<?php

namespace App\Tests\Functional;

use App\Entity\Catalog;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * L'API répond ce que promet le contrat docs/api/openapi.yaml (codes HTTP et forme du JSON).
 * Base de test : shopflow_test (voir README, section Tests).
 */
final class ApiTest extends WebTestCase
{
    private KernelBrowser $client;
    private int $servoId;
    private int $sensorId;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $db = static::getContainer()->get(Connection::class);
        $db->executeStatement('SET FOREIGN_KEY_CHECKS = 0');
        foreach (['order_catalog', '`order`', 'payment', 'cart_catalog', 'cart', 'catalog'] as $table) {
            $db->executeStatement("TRUNCATE TABLE $table");
        }
        $db->executeStatement('SET FOREIGN_KEY_CHECKS = 1');

        $servo = (new Catalog())->setName('Servomoteur SG90 9 g')->setPrice('3.20')->setStock(10)
            ->setCategory('Moteurs')->setLevel('Débutant')->setSpecs([['Couple', '1,8 kg·cm']]);
        $sensor = (new Catalog())->setName('Capteur à ultrasons HC-SR04')->setPrice('2.90')->setStock(0)
            ->setCategory('Capteurs')->setLevel('Débutant');
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->persist($sensor);
        $entityManager->persist($servo);
        $entityManager->flush();
        $this->servoId = (int) $servo->getId();
        $this->sensorId = (int) $sensor->getId();
    }

    public function testProductsAreListedWithOutOfStockLast(): void
    {
        $items = $this->get('/api/v1/products')['items'];

        $this->assertResponseIsSuccessful();
        $this->assertSame([$this->servoId, $this->sensorId], array_column($items, 'id'));
        $this->assertSame(3.2, $items[0]['price']);
    }

    public function testProductsCanBeFilteredByCategory(): void
    {
        $items = $this->get('/api/v1/products?category=Capteurs')['items'];

        $this->assertSame([$this->sensorId], array_column($items, 'id'));
    }

    public function testAnUnknownCategoryIsABadRequest(): void
    {
        $problem = $this->get('/api/v1/products?category=Robots');

        $this->assertResponseStatusCodeSame(400);
        $this->assertSame('Catégorie inconnue « Robots ».', $problem['detail']);
    }

    public function testAProductSheetHasItsTechnicalDetails(): void
    {
        $product = $this->get('/api/v1/products/' . $this->servoId);

        $this->assertResponseIsSuccessful();
        $this->assertSame('Servomoteur SG90 9 g', $product['name']);
        $this->assertSame([['Couple', '1,8 kg·cm']], $product['specs']);
    }

    public function testAnUnknownProductIsNotFound(): void
    {
        $problem = $this->get('/api/v1/products/999999');

        $this->assertResponseStatusCodeSame(404);
        $this->assertSame('Produit introuvable.', $problem['detail']);
    }

    public function testAnOrderIsPlacedFromTheCartAndCanBeReadBack(): void
    {
        $this->send('POST', '/api/v1/cart/items/' . $this->servoId, ['quantity' => 2]);
        $order = $this->send('POST', '/api/v1/orders', []);

        $this->assertResponseStatusCodeSame(201);
        $this->assertSame('pending_payment', $order['status']);
        $this->assertSame(6.4, $order['total']);
        $this->assertStringStartsWith('/payment/fake/', $order['paymentUrl']);
        $this->assertResponseHeaderSame('Location', '/api/v1/orders/' . $order['id']);

        $again = $this->get('/api/v1/orders/' . $order['id']);
        $this->assertResponseIsSuccessful();
        $this->assertSame(2, $again['lines'][0]['quantity']);
        $this->assertArrayNotHasKey('paymentUrl', $again);
    }

    public function testAnEmptyCartCannotBeOrdered(): void
    {
        $this->send('POST', '/api/v1/orders', []);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testPlacingAnOrderRequiresJson(): void
    {
        $this->client->request('POST', '/api/v1/orders', [], [], ['HTTP_ACCEPT' => 'application/json'], 'x');

        $this->assertResponseStatusCodeSame(415);
    }

    public function testAnUnknownOrderIsNotFound(): void
    {
        $this->get('/api/v1/orders/999999');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testTheContractIsServedForSwagger(): void
    {
        $this->client->request('GET', '/api/openapi.yaml');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('openapi: 3.1.0', (string) $this->client->getResponse()->getContent());
    }

    /**
     * @return array<string, mixed>
     */
    private function get(string $uri): array
    {
        $this->client->request('GET', $uri, [], [], ['HTTP_ACCEPT' => 'application/json']);

        return json_decode((string) $this->client->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);
    }

    /**
     * @param array<string, mixed> $body
     *
     * @return array<string, mixed>
     */
    private function send(string $method, string $uri, array $body): array
    {
        $this->client->request($method, $uri, [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ], json_encode((object) $body, \JSON_THROW_ON_ERROR));

        return json_decode((string) $this->client->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);
    }
}
