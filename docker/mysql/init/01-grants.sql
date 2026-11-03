-- Dev uniquement : l'utilisateur app peut créer/gérer shopflow_test (PHPUnit)
-- en plus de la base shopflow créée par l'image MySQL.
GRANT ALL PRIVILEGES ON `shopflow%`.* TO 'app'@'%';
FLUSH PRIVILEGES;
