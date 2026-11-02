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

```bash
docker compose up -d --build
```

- Site : http://localhost:8000 (page « Welcome to Symfony » tant qu'il n'y a pas de route)
- Mailpit, les mails envoyés par l'appli : http://localhost:8025
- MySQL : port 3307 de la machine, base `shopflow`, utilisateur `app` / `app` (mots de passe de dev)

Symfony et ses dépendances (`vendor/`) sont installés dans l'image Docker au moment du `--build`. Les dossiers `src/`, `templates/` et `config/` sont branchés sur vos fichiers : une modif dans l'éditeur se voit tout de suite, sans reconstruire. Après un `composer require`, relancez `docker compose up -d --build`.

Toujours lancer les commandes Symfony et Composer dans le conteneur, jamais sur sa machine :

```bash
docker compose exec php php bin/console about     # infos Symfony
docker compose exec php php bin/console make:entity  # créer une entité
docker compose exec php composer require <paquet>   # ajouter un paquet
docker compose down                               # tout arrêter (la base est gardée)
```

Si un port est déjà pris : `HTTP_PORT=8080 MYSQL_PORT=3308 docker compose up -d`.

Stack : Symfony 7.4 (webapp), PHP 8.3 + Apache, MySQL 8.4, Mailpit. Déploiement : uniquement en local avec Docker Compose pour l'instant.

## Jalon 1 : cadrer et modéliser

- [Exigences fonctionnelles et non fonctionnelles](docs/exigences.md)
- [C4 niveau 1, contexte](docs/c4/c4-contexte.png)
- [C4 niveau 2, conteneurs](docs/c4/c4-conteneurs.png) (encore à compléter)
- Source des schémas, à ouvrir avec draw.io : [docs/c4/shopflow-c4.drawio](docs/c4/shopflow-c4.drawio)
- [ADR-001 : choix de MySQL comme base de données](docs/adr/ADR-001-mysql.md)
- [ADR-002 : API REST JSON en PHP Symfony](docs/adr/ADR-002-api-rest-json.md)

## Jalon 2 : bounded contexts et modules

- [Context map](docs/ddd/context-map.png) (source draw.io : [docs/ddd/context-map.drawio](docs/ddd/context-map.drawio))
