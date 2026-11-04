# ADR-005 : ce qui reste synchrone

## Contexte

Aujourd’hui, passer une commande baisse le stock, fait la facture et envoie le mail à la suite.
Si le serveur mail tombe pendant une commande, toute la commande plante et le client ne peut pas acheter.
Nous devons choisir ce qui reste synchrone et ce qui peut passer en asynchrone.

## Décision

Nous avons décidé que la baisse du stock, la facture et le mail réagissent à l’événement OrderPlaced, en asynchrone, car personne n’a besoin d’avoir la réponse tout de suite.
La confirmation du paiement et la vérification du stock avant le paiement restent synchrones, car le client a besoin de la réponse tout de suite.

## Conséquences

Positives :

- Si le serveur mail tombe, la commande passe quand même : on attend juste qu’il revienne pour envoyer le mail.
- On peut ajouter une nouvelle réaction (un SMS par exemple) sans toucher au code de la commande.

Négatives :

- Un worker doit tourner en permanence pour traiter les messages.
- Si un message est envoyé deux fois, il ne doit être traité qu’une fois.
- Il faut mettre à jour le stock rapidement, pour éviter qu’un client commande un produit déjà en rupture mais pas encore mis à jour.

## Statut

Décision validée et applicable à l'architecture du projet.
