# ADR-007 : seuils et délais face aux pannes

## Contexte

Le paiement passe par un prestataire externe qui n’est pas toujours fiable : parfois il met 8 secondes à répondre, parfois il renvoie une erreur 500.
Pendant ce temps le client ne voit rien bouger, il pense que ça a buggé, il recharge la page et peut payer deux fois.
Si beaucoup de clients paient pendant une panne, ils attendent tous en même temps et le serveur peut planter, ce qui va contre notre ENF-02 (99 % de disponibilité).

## Décision

Nous avons décidé de protéger l’appel au paiement avec les réglages suivants :

- Temps d’attente maximum (timeout) : 3 secondes par essai, car au-delà le client pense que ça a buggé.
- Nouvelles tentatives : 2, après 1 seconde puis 2 secondes, car une erreur passagère se règle souvent toute seule. Au pire le client attend 12 secondes, c’est acceptable car on lui affiche « Paiement en cours, ne rechargez pas la page ».
- Disjoncteur : il s’ouvre après 3 échecs de suite chez 3 clients différents et reste ouvert 5 minutes avant un appel de test, car une panne de prestataire dure rarement moins de quelques minutes. Une carte refusée ne compte pas comme un échec : seuls les timeouts et les erreurs 500 comptent.
- Quand le paiement est indisponible : la commande passe en « en attente de paiement » et le client voit tout de suite que sa commande sera confirmée plus tard.
- Mail de confirmation : 3 nouvelles tentatives, puis le message est gardé dans la file des échecs et renvoyé quand le serveur mail revient, car le client a déjà la confirmation à l’écran.

## Conséquences

Positives :

- Le client n’a plus de page qui plante : il a toujours une réponse, même pendant une panne.
- Quand le prestataire est en panne on arrête de l’appeler, ça lui laisse le temps de revenir et notre serveur ne sature pas.
- Aucun mail n’est perdu.

Négatives :

- Des commandes restent « en attente de paiement » : il faut les relancer quand le prestataire revient.
- Réessayer un paiement peut débiter le client deux fois : chaque essai doit envoyer le même identifiant de commande pour que le prestataire ne débite qu’une fois.
- Si les seuils sont trop bas on coupe le paiement pour rien, s’ils sont trop hauts les clients attendent trop : il faudra les ajuster avec les vrais chiffres.

## Statut

Décision validée et applicable à l'architecture du projet.
