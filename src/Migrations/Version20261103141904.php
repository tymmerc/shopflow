<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261103141904 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Panier anonyme : jeton de cookie sur cart, user_id facultatif';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cart ADD token VARCHAR(32) NOT NULL, CHANGE user_id user_id INT DEFAULT NULL');
        // Paniers déjà présents : jeton aléatoire distinct, sinon l'index unique échoue sur les ''
        $this->addSql('UPDATE cart SET token = MD5(CONCAT(id, RAND()))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BA388B75F37A13B ON cart (token)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_BA388B75F37A13B ON cart');
        $this->addSql('ALTER TABLE cart DROP token, CHANGE user_id user_id INT NOT NULL');
    }
}
