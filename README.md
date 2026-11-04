# shopflow

Projet fil rouge du module « Architecture des systèmes logiciels » (parcours CLIC, Aflokkat, 2026-2027).

## Le brief

- Le client : une TPE qui vend en ligne des composants électroniques et des kits robotiques.
- Parcours complet : catalogue, panier, commande, paiement, livraison, notifications.
- Volume : 200 commandes par jour aujourd'hui, x10 visé à deux ans, pics pendant les soldes.
- Paiement par un prestataire externe (PSP, en mode test), expédition par un transporteur.
- Stack : Twig puis fetch sur l'API, PHP 8 / Symfony 7.4 (Doctrine, Messenger, HttpClient), MySQL ou PostgreSQL, Docker Compose, Mailpit, PHPUnit, Deptrac.

## Lancer le projet

Il faut Docker (Docker Desktop sur Mac ou Windows).

### Première fois

```bash
cp -n .env.example .env
docker compose up -d --build
docker compose exec php php bin/console doctrine:migrations:migrate -n
docker compose exec php php bin/console doctrine:fixtures:load -n
```

La 1re commande crée votre `.env` à partir du modèle (le `.env` n'est jamais poussé : vos valeurs à vous, comme une vraie clé, vont dedans). La 3e crée les tables, la 4e met les produits de démo (la boutique de robotique).

### Après chaque `git pull` (à faire à chaque fois)

Un `git pull` ne suffit pas : le code est copié dans l'image Docker, il faut la reconstruire, sinon vous voyez l'ancien site.

```bash
cp -n .env.example .env
docker compose up -d --build
docker compose exec php php bin/console doctrine:migrations:migrate -n
```

Si les produits ou les photos ont changé (vous voyez encore les anciens), rechargez les produits de démo. Attention, ça vide votre base locale (paniers et commandes de test compris) :

```bash
docker compose exec php php bin/console doctrine:fixtures:load -n
```

Puis rechargez la page sans cache : Ctrl+Shift+R (Cmd+Shift+R sur Mac).

### Adresses

- Site : http://localhost:8000
- API, à tester dans Swagger (bouton « Try it out ») : http://localhost:8000/api/docs
- Mailpit, les mails envoyés par l'appli : http://localhost:8025
- MySQL : port 3307 de la machine, base `shopflow`, utilisateur `app` / `app` (mots de passe de dev)

Symfony et ses dépendances (`vendor/`) sont installés dans l'image Docker au moment du `--build`, avec votre `.env` : après l'avoir modifié, relancez `docker compose up -d --build`. Les dossiers `src/`, `templates/` et `config/` sont branchés sur vos fichiers : une modif dans l'éditeur se voit tout de suite, sans reconstruire. Après un `composer require`, relancez `docker compose up -d --build`.

Toujours lancer les commandes Symfony et Composer dans le conteneur, jamais sur sa machine :

```bash
docker compose exec php php bin/console about     # infos Symfony
docker compose exec php php bin/console make:entity  # créer une entité
docker compose exec php composer require <paquet>   # ajouter un paquet
docker compose down                               # tout arrêter (la base est gardée)
```

Si un port est déjà pris : `HTTP_PORT=8080 MYSQL_PORT=3308 docker compose up -d`.

Stack : Symfony 7.4 (webapp), PHP 8.3 + Apache, MySQL 8.4, Mailpit. Déploiement : uniquement en local avec Docker Compose pour l'instant.

### Paiement et e-mails en local

Aucun vrai prestataire de paiement n'est branché : en dev, « Passer commande » mène à une page de paiement simulée (`/payment/fake/…`) avec les boutons Payer et Refuser. Elle n'existe pas en production.

Les e-mails partent en file (Messenger) et sont envoyés par le service `worker`, puis arrivent dans Mailpit. Après une modification de `config/`, relancez-le : `docker compose restart worker`.

## Tester l'API à la main

Les réponses sont en JSON ; `| jq` les affiche lisibles (sans jq : `| python3 -m json.tool`).
Le panier est lié à la session, comme sur le site : `-c cookies.txt -b cookies.txt` garde le cookie d'une commande à l'autre.

```bash
# Produits
curl -s localhost:8000/api/v1/products | jq
curl -s "localhost:8000/api/v1/products?category=Capteurs&level=Débutant" | jq
curl -s localhost:8000/api/v1/products/14 | jq

# Panier
curl -s -c cookies.txt -b cookies.txt localhost:8000/api/v1/cart | jq
curl -s -c cookies.txt -b cookies.txt -X POST localhost:8000/api/v1/cart/items/14 -H "Content-Type: application/json" -d '{"quantity": 2}' | jq
curl -s -c cookies.txt -b cookies.txt -X PUT localhost:8000/api/v1/cart/items/14 -H "Content-Type: application/json" -d '{"quantity": 3}' | jq
curl -s -c cookies.txt -b cookies.txt -X DELETE localhost:8000/api/v1/cart/items/14 | jq

# Commande (le panier ne doit pas être vide), puis la relire avec l'id reçu
curl -s -c cookies.txt -b cookies.txt -X POST localhost:8000/api/v1/orders -H "Content-Type: application/json" -d '{}' | jq
curl -s -b cookies.txt localhost:8000/api/v1/orders/1 | jq

# Erreurs : 400 catégorie inconnue, 404 produit inconnu, 415 corps pas en JSON
curl -s "localhost:8000/api/v1/products?category=Robots" | jq
curl -s localhost:8000/api/v1/products/999 | jq
curl -s -X POST localhost:8000/api/v1/orders -d 'x' | jq
```

## Tests

La première fois, créez la base de test `shopflow_test` :

```bash
docker compose exec php php bin/console doctrine:database:create --env=test --if-not-exists
docker compose exec php php bin/console doctrine:migrations:migrate --env=test -n
```

Si MySQL refuse l'accès à `shopflow_test` (volume créé avant `docker/mysql/init/01-grants.sql`), donnez le droit une fois :

```bash
docker compose exec database mysql -uroot -proot -e "GRANT ALL PRIVILEGES ON \`shopflow%\`.* TO 'app'@'%'"
```

Puis, à chaque fois :

```bash
docker compose exec php php bin/phpunit                  # tous les tests
docker compose exec php php bin/phpunit tests/Functional # parcours de commande complet
```

Après une nouvelle migration, rejouez la commande `doctrine:migrations:migrate --env=test -n`.

## Jalon 1 : cadrer et modéliser

- [Exigences fonctionnelles et non fonctionnelles](docs/exigences.md)
- [C4 niveau 1, contexte](docs/c4/c4-contexte.png)
- [C4 niveau 2, conteneurs](docs/c4/c4-conteneurs.png) (encore à compléter)
- Source des schémas, à ouvrir avec draw.io : [docs/c4/shopflow-c4.drawio](docs/c4/shopflow-c4.drawio)
- [ADR-001 : choix MySQL comme base de données](docs/adr/ADR-001-mysql.md)
- [ADR-002 : API REST JSON en PHP Symfony](docs/adr/ADR-002-api-rest-json.md)

## Jalon 2 : bounded contexts et modules

- [ADR-003 : architecture monolithique](docs/adr/ADR-003-monolithe.md)
- [Context map](docs/ddd/context-map.png) (source draw.io : [docs/ddd/context-map.drawio](docs/ddd/context-map.drawio))

## Jalon 3 : isoler le cœur Commande

- [ADR-004 : périmètre de l'architecture hexagonale](docs/adr/ADR-004-perimetre-hexagonal.md)
- Le module Commande en hexagone : [src/Order](src/Order) (Domain, Application avec ses ports, Infrastructure, UI)
- Tests du domaine, sans base de données ni Symfony : [tests/Order/Domain](tests/Order/Domain)

## Jalon 4 : événements

- [ADR-005 : ce qui reste synchrone](docs/adr/ADR-005-synchrone-asynchrone.md)

## Jalon 5 : API REST et messages

- [Contrat OpenAPI](docs/api/openapi.yaml), consultable dans Swagger sur http://localhost:8000/api/docs
- [ADR-006 : REST plutôt que GraphQL pour le partenaire](docs/adr/ADR-006-rest-plutot-que-graphql.md)

## Jalon 6 : préparer ShopFlow à la panne

- [ADR-007 : seuils et délais face aux pannes](docs/adr/ADR-007-seuils-et-delais-pannes.md)
