<?php

namespace App\DataFixtures;

use App\Entity\Catalog;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Produits de démo pour le dev : une boutique de robotique et de composants électroniques.
 * Photos libres de droit (Wikimedia Commons), crédits sur la page /credits.
 * Charger : docker compose exec php php bin/console doctrine:fixtures:load
 * (vide la base avant de la remplir)
 */
class CatalogFixtures extends Fixture
{
    private const IMG = '/images/products/';

    public function load(ObjectManager $manager): void
    {
        foreach (self::products() as $data) {
            $product = (new Catalog())
                ->setName($data['name'])
                ->setDesc($data['desc'])
                ->setStock($data['stock'])
                ->setPrice($data['price'])
                ->setImg(self::IMG . $data['img'])
                ->setCategory($data['category'])
                ->setLevel($data['level'])
                ->setTagline($data['tagline'])
                ->setSolderingRequired($data['soldering'])
                ->setSpecs(self::pairs($data['specs']))
                ->setCompatibility($data['compatibility'])
                ->setBoxContents($data['box'])
                ->setAccessories($data['accessories']);

            $manager->persist($product);
        }

        $manager->flush();
    }

    /**
     * MySQL range les clés d'un objet JSON dans son propre ordre : on stocke une liste de paires
     * [libellé, valeur] pour garder l'ordre de lecture de la fiche.
     *
     * @param array<string, string> $specs
     *
     * @return list<array{0: string, 1: string}>
     */
    private static function pairs(array $specs): array
    {
        return array_map(static fn (string $label, string $value) => [$label, $value], array_keys($specs), array_values($specs));
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function products(): array
    {
        return [
            [
                'name' => 'Carte Arduino Uno R3 compatible',
                'category' => 'Cartes',
                'level' => 'Débutant',
                'tagline' => 'Le cerveau de votre premier robot : elle lit les capteurs et commande les moteurs.',
                'desc' => 'Carte compatible Arduino Uno R3 à microcontrôleur ATmega328P. Se programme en USB depuis l\'IDE Arduino, avec des milliers d\'exemples et de bibliothèques disponibles.',
                'price' => '14.90', 'stock' => 42, 'img' => 'arduino-uno-r3.jpg', 'soldering' => false,
                'specs' => [
                    'Microcontrôleur' => 'ATmega328P à 16 MHz',
                    'Tension de fonctionnement' => '5 V',
                    'Alimentation' => '7 à 12 V (jack) ou USB',
                    'E/S numériques' => '14 (dont 6 PWM)',
                    'Entrées analogiques' => '6',
                    'Mémoire flash' => '32 Ko',
                    'Dimensions' => '68,6 × 53,4 mm',
                    'Poids' => '25 g',
                ],
                'compatibility' => ['Shields Arduino Uno', 'IDE Arduino', 'PlatformIO'],
                'box' => ['1 carte Uno R3 compatible', '1 câble USB-B de 30 cm'],
                'accessories' => ['Lot de 40 câbles Dupont mâle-mâle', 'Capteur à ultrasons HC-SR04', 'Servomoteur SG90 9 g'],
            ],
            [
                'name' => 'Arduino Uno R4 WiFi',
                'category' => 'Cartes',
                'level' => 'Intermédiaire',
                'tagline' => 'Une Uno qui se connecte au Wi-Fi pour piloter votre robot depuis le téléphone.',
                'desc' => 'Carte officielle Arduino avec microcontrôleur Renesas RA4M1 32 bits, module ESP32-S3 pour le Wi-Fi et le Bluetooth, et une matrice de 96 LED rouges sur la carte.',
                'price' => '27.50', 'stock' => 4, 'img' => 'arduino-uno-r4-wifi.jpg', 'soldering' => false,
                'specs' => [
                    'Microcontrôleur' => 'Renesas RA4M1 à 48 MHz',
                    'Connectivité' => 'Wi-Fi 2,4 GHz, Bluetooth LE (ESP32-S3)',
                    'Tension de fonctionnement' => '5 V',
                    'Alimentation' => '6 à 24 V (jack) ou USB-C',
                    'E/S numériques' => '14 (dont 6 PWM)',
                    'Mémoire flash' => '256 Ko',
                    'Dimensions' => '68,9 × 53,4 mm',
                    'Poids' => '27 g',
                ],
                'compatibility' => ['Shields Arduino Uno', 'IDE Arduino 2', 'Arduino Cloud'],
                'box' => ['1 carte Arduino Uno R4 WiFi'],
                'accessories' => ['Lot de 40 câbles Dupont mâle-mâle', 'Moteur pas à pas NEMA 17'],
            ],
            [
                'name' => 'Raspberry Pi 4 Model B 4 Go',
                'category' => 'Cartes',
                'level' => 'Confirmé',
                'tagline' => 'Un vrai petit ordinateur pour donner la vision et l\'intelligence à un robot.',
                'desc' => 'Ordinateur monocarte sous Linux avec processeur 4 cœurs, 4 Go de RAM, deux sorties micro-HDMI 4K, Wi-Fi, Bluetooth et Gigabit Ethernet. Idéal pour le traitement d\'image avec une caméra.',
                'price' => '62.90', 'stock' => 0, 'img' => 'raspberry-pi-4.jpg', 'soldering' => false,
                'specs' => [
                    'Processeur' => 'Broadcom BCM2711, 4 × Cortex-A72 à 1,8 GHz',
                    'Mémoire' => '4 Go LPDDR4',
                    'Alimentation' => '5 V, 3 A (USB-C)',
                    'GPIO' => '40 broches, 3,3 V',
                    'Bus' => 'I2C, SPI, UART',
                    'Connectivité' => 'Wi-Fi 5, Bluetooth 5.0, Gigabit Ethernet',
                    'Dimensions' => '85 × 56 mm',
                    'Poids' => '46 g',
                ],
                'compatibility' => ['Raspberry Pi OS', 'Python', 'Cartes HAT'],
                'box' => ['1 carte Raspberry Pi 4 Model B 4 Go'],
                'accessories' => ['Lot de 40 câbles Dupont mâle-mâle'],
            ],
            [
                'name' => 'Module ESP32-WROOM-32',
                'category' => 'Cartes',
                'level' => 'Confirmé',
                'tagline' => 'Ajoute le Wi-Fi et le Bluetooth à un objet connecté, pour quelques euros.',
                'desc' => 'Module Espressif à double cœur Xtensa avec Wi-Fi et Bluetooth intégrés. Module seul à souder sur votre circuit : pour débuter, préférez une carte de développement ESP32.',
                'price' => '4.60', 'stock' => 120, 'img' => 'esp32-wroom-32.jpg', 'soldering' => true,
                'specs' => [
                    'Processeur' => '2 × Xtensa LX6 à 240 MHz',
                    'Connectivité' => 'Wi-Fi 802.11 b/g/n, Bluetooth 4.2 et BLE',
                    'Tension de fonctionnement' => '3,3 V',
                    'Mémoire flash' => '4 Mo',
                    'Bus' => 'I2C, SPI, UART, CAN',
                    'Dimensions' => '18 × 25,5 mm',
                    'Poids' => '2 g',
                ],
                'compatibility' => ['IDE Arduino (cœur ESP32)', 'ESP-IDF', 'MicroPython'],
                'box' => ['1 module ESP32-WROOM-32'],
                'accessories' => ['Fer à souder USB-C SH72'],
            ],
            [
                'name' => 'Servomoteur SG90 9 g',
                'category' => 'Moteurs',
                'level' => 'Débutant',
                'tagline' => 'Fait tourner une pince ou une tête de robot à l\'angle exact que vous choisissez.',
                'desc' => 'Micro servomoteur léger, rotation sur 180°, piloté par un simple signal PWM. Parfait pour les bras robotisés, les pinces et les petites directions.',
                'price' => '3.20', 'stock' => 86, 'img' => 'servo-sg90.jpg', 'soldering' => false,
                'specs' => [
                    'Couple' => '1,8 kg·cm à 4,8 V',
                    'Vitesse' => '0,1 s / 60°',
                    'Tension de fonctionnement' => '4,8 à 6 V',
                    'Angle' => '180°',
                    'Commande' => 'PWM, 50 Hz',
                    'Connecteur' => 'JR 3 broches',
                    'Dimensions' => '22,2 × 11,8 × 31 mm',
                    'Poids' => '9 g',
                ],
                'compatibility' => ['Arduino Uno', 'ESP32', 'Raspberry Pi 4'],
                'box' => ['1 servomoteur SG90', '3 palonniers', '2 vis de fixation'],
                'accessories' => ['Carte Arduino Uno R3 compatible', 'Lot de 40 câbles Dupont mâle-mâle'],
            ],
            [
                'name' => 'Moteur pas à pas NEMA 17',
                'category' => 'Moteurs',
                'level' => 'Intermédiaire',
                'tagline' => 'Déplace un axe au millimètre près : imprimante 3D, traceur ou bras robotisé.',
                'desc' => 'Moteur pas à pas bipolaire au format NEMA 17, 200 pas par tour. Se pilote avec un driver type A4988 ou DRV8825 et une carte Arduino.',
                'price' => '11.40', 'stock' => 18, 'img' => 'nema-17.jpg', 'soldering' => false,
                'specs' => [
                    'Type' => 'Bipolaire, 4 fils',
                    'Pas' => '1,8° (200 pas par tour)',
                    'Couple de maintien' => '0,4 N·m',
                    'Courant par phase' => '1,5 A',
                    'Axe' => '5 mm, méplat',
                    'Dimensions' => '42 × 42 × 40 mm',
                    'Poids' => '280 g',
                ],
                'compatibility' => ['Drivers A4988 et DRV8825', 'Arduino Uno', 'Cartes d\'imprimante 3D'],
                'box' => ['1 moteur NEMA 17', '1 câble 4 fils de 1 m'],
                'accessories' => ['Carte Arduino Uno R3 compatible', 'Lot de 10 câbles à pinces crocodile'],
            ],
            [
                'name' => 'Capteur à ultrasons HC-SR04',
                'category' => 'Capteurs',
                'level' => 'Débutant',
                'tagline' => 'Les yeux du robot : il mesure la distance d\'un obstacle pour l\'éviter.',
                'desc' => 'Capteur de distance par ultrasons, de 2 cm à 4 m. Deux broches suffisent (TRIG et ECHO) : c\'est le capteur classique des robots éviteurs d\'obstacles.',
                'price' => '2.90', 'stock' => 3, 'img' => 'hc-sr04.jpg', 'soldering' => false,
                'specs' => [
                    'Portée' => '2 cm à 4 m',
                    'Précision' => '± 3 mm',
                    'Angle de mesure' => '15°',
                    'Tension de fonctionnement' => '5 V',
                    'Courant' => '15 mA',
                    'Broches' => 'VCC, TRIG, ECHO, GND',
                    'Dimensions' => '45 × 20 × 15 mm',
                    'Poids' => '9 g',
                ],
                'compatibility' => ['Arduino Uno', 'ESP32 (avec pont diviseur)', 'Raspberry Pi 4 (avec pont diviseur)'],
                'box' => ['1 capteur HC-SR04'],
                'accessories' => ['Carte Arduino Uno R3 compatible', 'Lot de 40 câbles Dupont mâle-mâle'],
            ],
            [
                'name' => 'Lot de 40 câbles Dupont mâle-mâle',
                'category' => 'Câblage',
                'level' => 'Débutant',
                'tagline' => 'Relie la carte, les capteurs et la plaque d\'essai sans rien souder.',
                'desc' => 'Nappe de 40 câbles de couleurs séparables, embouts mâle-mâle au pas de 2,54 mm. Pour prototyper sur plaque d\'essai.',
                'price' => '3.90', 'stock' => 150, 'img' => 'cables-dupont.jpg', 'soldering' => false,
                'specs' => [
                    'Quantité' => '40 câbles',
                    'Longueur' => '20 cm',
                    'Embouts' => 'Mâle-mâle, pas de 2,54 mm',
                    'Section' => '26 AWG',
                    'Poids' => '30 g',
                ],
                'compatibility' => ['Plaques d\'essai', 'Arduino Uno', 'Raspberry Pi 4 (avec embouts femelles)'],
                'box' => ['40 câbles en nappe'],
                'accessories' => ['Carte Arduino Uno R3 compatible', 'Capteur à ultrasons HC-SR04'],
            ],
            [
                'name' => 'Lot de 10 câbles à pinces crocodile',
                'category' => 'Câblage',
                'level' => 'Débutant',
                'tagline' => 'Branche un moteur ou une pile en un geste pour tester un montage.',
                'desc' => 'Dix câbles avec une pince crocodile isolée à chaque bout, en cinq couleurs. Pratiques pour les tests rapides et les mesures au multimètre.',
                'price' => '4.50', 'stock' => 64, 'img' => 'cables-crocodile.jpg', 'soldering' => false,
                'specs' => [
                    'Quantité' => '10 câbles',
                    'Longueur' => '50 cm',
                    'Pinces' => 'Crocodile isolées, ouverture 7 mm',
                    'Courant max' => '3 A',
                    'Poids' => '85 g',
                ],
                'compatibility' => ['Moteurs', 'Piles et batteries', 'Multimètres'],
                'box' => ['10 câbles à pinces crocodile'],
                'accessories' => ['Moteur pas à pas NEMA 17'],
            ],
            [
                'name' => 'Fer à souder USB-C SH72',
                'category' => 'Outillage',
                'level' => 'Intermédiaire',
                'tagline' => 'Pour souder les broches d\'un module ou réparer un câble, partout.',
                'desc' => 'Fer à souder compact alimenté en USB-C, montée en température en 10 s, réglable de 100 à 400 °C. Il tient dans une trousse.',
                'price' => '39.90', 'stock' => 9, 'img' => 'fer-a-souder-sh72.jpg', 'soldering' => false,
                'specs' => [
                    'Puissance' => '65 W',
                    'Température' => '100 à 400 °C',
                    'Chauffe' => '10 s jusqu\'à 300 °C',
                    'Alimentation' => 'USB-C PD 20 V ou 12 à 24 V (jack)',
                    'Panne' => 'Type TS, interchangeable',
                    'Longueur' => '160 mm',
                    'Poids' => '33 g',
                ],
                'compatibility' => ['Chargeurs USB-C PD 65 W', 'Pannes TS'],
                'box' => ['1 fer à souder SH72', '1 panne fine', '1 support'],
                'accessories' => ['Module ESP32-WROOM-32'],
            ],
        ];
    }
}
