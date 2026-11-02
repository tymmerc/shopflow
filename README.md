# shopflow

Projet fil rouge du module « Architecture des systèmes logiciels » (parcours CLIC, Aflokkat, 2026-2027).

## Le brief

- Le client : une TPE qui vend en ligne des composants électroniques et des kits robotiques.
- Parcours complet : catalogue, panier, commande, paiement, livraison, notifications.
- Volume : 200 commandes par jour aujourd'hui, x10 visé à deux ans, pics pendant les soldes.
- Paiement par un prestataire externe (PSP, en mode test), expédition par un transporteur.
- Stack : Twig puis fetch sur l'API, PHP 8 / Symfony 7.4 (Doctrine, Messenger, HttpClient), MySQL ou PostgreSQL, Docker Compose, Mailpit, PHPUnit, Deptrac.

## Jalon 1 : cadrer et modéliser

### Exigences fonctionnelles

| # | En tant que | Je veux | Afin de |
|---|---|---|---|
| EF-01 | utilisateur | consulter le catalogue | |
| EF-02 | administrateur | consulter des données en rapport avec mes ventes | avoir une vision sur ma boutique |
| EF-03 | utilisateur | créer un compte | |
| EF-04 | utilisateur | consulter l’historique de mes commandes | |
| EF-05 | utilisateur | filtrer mes résultats | affiner ma recherche |
| EF-06 | administrateur | gérer les articles | |
| EF-07 | utilisateur | ajouter un article à mon panier | |
| EF-08 | utilisateur | consulter et modifier mon panier | |
| EF-09 | utilisateur | effectuer le paiement de mon panier | |
| EF-10 | utilisateur | recevoir un mail de confirmation de paiement | notifier le client que sa commande est passée |
| EF-11 | administrateur | Gérer le stock | |

### Exigences non fonctionnelles

| # | Catégorie | Exigence | Objectif |
|---|---|---|---|
| ENF-01 | performance | affichage du catalogue en -2s dans 99% des cas | |
| ENF-02 | disponibilité | 99% de disponibilité | ne pas perdre de clients |
| ENF-03 | performance | Serveur web peut gérer au moins 1000 requêtes simultanés | il craque pas |
| ENF-04 | sécurité | Paiement avec api externe sécurisé | Paiement sécurisé |
| ENF-05 | RGPD | respecter les exigences du rgpd | éviter les pbs avec la justice |
| ENF-06 | Sauvegarde | Le système doit garantir l'intégrité des données clients, commandes, paiements et stocks et de restaurer les données après un incident | éviter les pertes de données |
| ENF-07 | Compatibilité | Le site doit fonctionner sur les dernières versions des principaux navigateurs | |

### Modèle C4

- Niveau 1 (contexte) : à venir.
- Niveau 2 (conteneurs) : à venir.

### Décisions d'architecture (ADR)

- ADR-001 : stack et style d'architecture de départ, à venir.
