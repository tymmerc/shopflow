# ADR-006 : REST plutôt que GraphQL pour le partenaire

## Contexte

Une application mobile partenaire veut lire notre catalogue et passer des commandes.
Nous avons peu de ressources : un seul partenaire et une petite équipe.
Dans l'ADR-002 nous avons déjà choisi une API REST en JSON pour notre propre front.

## Décision

Nous avons décidé d’ouvrir au partenaire une API REST en JSON, sous /api/v1, car c’est ce que nous utilisons déjà pour le panier et toute l’équipe sait s’en servir.
Le partenaire pourra lire le catalogue, passer une commande et suivre son état.
L’API sera décrite dans un fichier openapi.yaml, pour que le partenaire sache comment l’utiliser sans nous demander.
GraphQL est écarté, car il est trop complexe et que nous utilisions déjà REST.
gRPC est écarté, car il demande des outils en plus et ses messages ne sont pas lisibles à l’œil.

## Conséquences

Positives :

- On réutilise ce qu’on connaît déjà : pas de nouvel outil à apprendre.
- Le partenaire peut lire la documentation dans Swagger et se brancher tout seul.

Négatives :

- Le partenaire reçoit parfois plus de données qu’il n’en a besoin, ou doit faire plusieurs appels pour afficher un seul écran.
- Si on change une route, l’application du partenaire peut planter : il faut créer une nouvelle version (/api/v2) et la documenter.

## Statut

Décision validée et applicable à l'architecture du projet.
