# ADR-003 : Choix d'une architecture monolithique pour l'application

## Contexte

Dans le cadre du développement de l'application, nous devons définir une architecture logicielle adaptée aux besoins fonctionnels et techniques du projet.
Plusieurs approches architecturales sont envisageables, notamment une architecture monolithique et une architecture microservices.
Compte tenu de la taille actuelle du projet, du périmètre fonctionnel, des ressources disponibles et de la nécessité de faciliter le développement et la maintenance, une architecture simple et centralisée apparaît comme la solution la plus adaptée.

## Décision

Nous avons décidé d'adopter une architecture monolithique pour le développement de l'application.
L'ensemble des fonctionnalités sera regroupé au sein d'une seule application, organisée en différents modules logiques afin de séparer les responsabilités et de faciliter la maintenance du code.
Cette approche permettra de centraliser la logique métier, de simplifier les interactions entre les différents composants et de faciliter le déploiement de l'application.

## Conséquences

Positives :

- Simplicité de développement : une seule application à développer et à maintenir.
- Facilité de déploiement : un seul livrable à construire et à déployer.
- Communication simplifiée : les différents modules communiquent directement au sein de l'application.
- Maintenance facilitée : centralisation du code et simplification du suivi des fonctionnalités.
- Coûts réduits : moins d'infrastructure et moins de complexité opérationnelle qu'une architecture microservices.
- Cohérence des données : possibilité de centraliser la gestion des transactions et des accès à la base de données.

Négatives :

- Scalabilité limitée : il n'est pas possible de faire évoluer indépendamment chaque fonctionnalité.
- Dépendance entre les modules : une modification dans un module peut avoir des répercussions sur les autres si les responsabilités sont mal séparées.
- Déploiement global : toute modification nécessite généralement de redéployer l'ensemble de l'application.
- Risque de complexification : si l'application grandit sans organisation modulaire rigoureuse, le code peut devenir difficile à maintenir.

## Statut

Le choix d'une architecture monolithique est validé pour le développement de l'application.
Cette décision est adaptée au périmètre actuel du projet et pourra être réévaluée si les besoins en matière de scalabilité, de performance ou d'organisation évoluent significativement.
