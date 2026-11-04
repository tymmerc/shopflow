<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261103163351 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Commande : statut, date et panier d\'origine';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // Ajout en nullable puis remplissage : sinon échec si des commandes existent déjà
        $this->addSql('ALTER TABLE `order` ADD cart_id INT DEFAULT NULL, ADD status VARCHAR(20) DEFAULT NULL, ADD placed_at DATETIME DEFAULT NULL');
        $this->addSql("UPDATE `order` SET status = 'pending_payment', placed_at = NOW()");
        $this->addSql('ALTER TABLE `order` MODIFY status VARCHAR(20) NOT NULL, MODIFY placed_at DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` DROP cart_id, DROP status, DROP placed_at');
    }
}
