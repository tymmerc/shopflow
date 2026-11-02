# ADR-001 : choix de MySQL comme base de données

## Contexte

L’application doit gérer des utilisateurs, produits, commandes, paniers et paiements.
Les données présentent des relations complexes et nécessitent une bonne cohérence transactionnelle.
Nous devons choisir un système de gestion de base de données adapté à ces besoins.

## Décision

Nous avons décidé d'utiliser MySQL comme système de gestion de base de données.

## Conséquences

Positives :

- Garantit la cohérence des données.
- Permet de gérer des relations complexes.
- Offre des transactions fiables.

Négatives :

- Nécessite une administration spécifique.
- Implique de maîtriser la modélisation relationnelle.

## Statut

Décision validée et applicable à l'architecture du projet.
