# ADR-005 : ce qui reste synchrone

## Contexte

Aujourd’hui, passer une commande baisse le stock, fait la facture et envoie le mail à la suite.
Si le serveur mail tombe pendant une commande, toute la commande plante et le client ne peut pas acheter.
Nous devons choisir ce qui reste synchrone et ce qui peut passer en asynchrone.

## Décision

Nous avons décidé que le mail de confirmation (et plus tard la facture) part en asynchrone : quand la commande est payée, elle publie l’événement OrderPaid et le mail passe par la file de Messenger, car personne n’a besoin de la réponse tout de suite.
La confirmation du paiement et la baisse du stock restent synchrones : le stock baisse au moment où le paiement est accepté, pour tous les articles en une fois, et la commande est annulée s’il en manque un. Le client a besoin de le savoir tout de suite.

## Conséquences

Positives :

- Si le serveur mail tombe, la commande passe quand même : on attend juste qu’il revienne pour envoyer le mail.
- On peut ajouter une nouvelle réaction (un SMS par exemple) sans toucher au code de la commande.

Négatives :

- Un worker doit tourner en permanence pour traiter les messages.
- Si un message est envoyé deux fois, il ne doit être traité qu’une fois.
- La baisse du stock se fait pendant la confirmation du paiement : si la base de données est lente, la confirmation l’est aussi.

## Statut

Décision validée et applicable à l'architecture du projet.
