<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261103105859 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cart (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE cart_catalog (id INT AUTO_INCREMENT NOT NULL, quantity INT NOT NULL, cart_id INT NOT NULL, catalog_id INT NOT NULL, UNIQUE INDEX uniq_cart_catalog (cart_id, catalog_id), INDEX IDX_E0F6FA401AD5CDBF (cart_id), INDEX IDX_E0F6FA40CC3C66FC (catalog_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE cart_catalog ADD CONSTRAINT FK_E0F6FA401AD5CDBF FOREIGN KEY (cart_id) REFERENCES cart (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cart_catalog ADD CONSTRAINT FK_E0F6FA40CC3C66FC FOREIGN KEY (catalog_id) REFERENCES catalog (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cart_catalog DROP FOREIGN KEY FK_E0F6FA401AD5CDBF');
        $this->addSql('ALTER TABLE cart_catalog DROP FOREIGN KEY FK_E0F6FA40CC3C66FC');
        $this->addSql('DROP TABLE cart');
        $this->addSql('DROP TABLE cart_catalog');
    }
}
