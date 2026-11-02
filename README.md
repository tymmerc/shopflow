# shopflow

Projet fil rouge du module « Architecture des systèmes logiciels » (parcours CLIC, Aflokkat, 2026-2027).

## Le brief

- Le client : une TPE qui vend en ligne des composants électroniques et des kits robotiques.
- Parcours complet : catalogue, panier, commande, paiement, livraison, notifications.
- Volume : 200 commandes par jour aujourd'hui, x10 visé à deux ans, pics pendant les soldes.
- Paiement par un prestataire externe (PSP, en mode test), expédition par un transporteur.
- Stack : Twig puis fetch sur l'API, PHP 8 / Symfony 7.4 (Doctrine, Messenger, HttpClient), MySQL ou PostgreSQL, Docker Compose, Mailpit, PHPUnit, Deptrac.

## Jalon 1 : cadrer et modéliser

- [Exigences fonctionnelles et non fonctionnelles](docs/exigences.md)
- [C4 niveau 1, contexte](docs/c4/c4-contexte.png)
- [C4 niveau 2, conteneurs](docs/c4/c4-conteneurs.png) (encore à compléter)
- Source des schémas, à ouvrir avec draw.io : [docs/c4/shopflow-c4.drawio](docs/c4/shopflow-c4.drawio)
- [ADR-001 : choix de MySQL comme base de données](docs/adr/ADR-001-mysql.md) (en cours)
