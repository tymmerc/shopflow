# ADR-002 : choix d'une API REST JSON en PHP Symfony

## Contexte

L’application doit afficher le catalogue, le panier et les commandes dans le navigateur.
Le front doit pouvoir échanger des données avec le back sans recharger toute la page.
Nous devons choisir un moyen de communication adapté entre le front et le back.

## Décision

Nous avons décidé d'exposer une API REST en JSON avec Symfony.

## Conséquences

Positives :

- Sépare le front et le back.
- Utilise un format standard et lisible.
- Facilite les tests de l’application.

Négatives :

- Nécessite de sécuriser l’API.
- Implique de documenter chaque route.

## Statut

Décision validée et applicable à l'architecture du projet.
