<?php

namespace App\DataFixtures;

use App\Entity\Catalog;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Produits de démo pour le dev.
 * Charger : docker compose exec php php bin/console doctrine:fixtures:load
 * (vide la base avant de la remplir)
 */
class CatalogFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $products = [
            ['T-shirt ShopFlow', 'T-shirt 100 % coton bio, coupe unisexe.', 25, '19.99', null],
            ['Mug céramique', 'Mug 33 cl, passe au lave-vaisselle.', 40, '12.50', null],
            ['Sac en toile', 'Tote bag en coton recyclé, anses longues.', 15, '9.90', null],
        ];

        foreach ($products as [$name, $desc, $stock, $price, $img]) {
            $product = (new Catalog())
                ->setName($name)
                ->setDesc($desc)
                ->setStock($stock)
                ->setPrice($price)
                ->setImg($img);

            $manager->persist($product);
        }

        $manager->flush();
    }
}
