# ADR-004 : périmètre de l'architecture hexagonale

## Contexte

La Commande et le Paiement portent de vraies règles métier à tester : refuser un panier vide, vérifier le stock, calculer un total juste, n'accepter un paiement que s'il est en attente.
Le Catalogue et le Panier ne font qu'afficher et enregistrer des données, sans règle métier notable.
Appliquer partout la même architecture coûterait cher là où il n'y a rien à protéger.

## Décision

Nous avons décidé d'appliquer l'architecture hexagonale (Domain / Application / Infrastructure / UI, avec des ports et des adaptateurs) uniquement aux modules qui portent des règles métier :

- **Order** : oui, car il contient les règles de la commande (panier vide, stock, total, statuts).
- **Payment** : oui, car le prestataire de paiement est externe et doit pouvoir être remplacé.
- **Notification** : oui, car l'envoi de mail passe par un port pour être testé sans serveur SMTP.
- **Catalog** et **Cart** : non, car ce n'est que de l'affichage et de l'enregistrement (CRUD), le Symfony classique (Controller / Entity / Repository) suffit.

Les dépendances entre modules sont contrôlées par Deptrac (`deptrac.yaml`).

## Conséquences

Positives :

- Les règles de la Commande et du Paiement se testent sans base de données ni réseau, grâce aux adaptateurs en mémoire (`InMemoryOrderRepository`, `InMemoryPaymentRepository`, `InMemoryStock`).
- On peut changer de prestataire de paiement en écrivant un nouvel adaptateur du port `PaymentGateway`, sans toucher à la Commande.
- Le Catalogue et le Panier restent simples et rapides à faire évoluer.

Négatives :

- Plus de fichiers pour chaque dépendance externe : un port, un adaptateur réel (Doctrine, Mailer) et une version de test (InMemory).
- Deux façons de ranger le code cohabitent dans le même projet (`src/Order/Domain/...` d'un côté, `src/Controller` et `src/Entity` de l'autre), ce qui demande de savoir où placer un nouveau code.

## Statut

Décision validée et applicable à l'architecture du projet.
