# ADR-002 : choix de PHP Symfony

## Contexte

L’application doit afficher le catalogue, le panier et les commandes dans le navigateur.
Le front doit pouvoir échanger des données avec le back sans recharger toute la page.
Nous devons choisir un moyen de communication adapté entre le front et le back.

## Décision

Nous avons décidé d'opter pour une architecture monolithique utilisant le framework Symfony avec un rendu côté serveur.

## Conséquences

Positives

- Un seul dépôt de code et un déploiement unifié pour le front et le back.

- Gestion simplifiée des sessions et des accès sans avoir à sécuriser des tokens d'API (JWT/Sanctum).

- Idéal pour un développement rapide sans surcoût d'architecture.

Négatives

- Le front-end et le back-end sont étroitement liés dans le même projet.

- Moins flexible si l'on souhaite développer plus tard une application mobile ou un autre front-end séparé.

## Statut

Décision validée et applicable à l'architecture du projet.
