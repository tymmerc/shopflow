<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261103145040 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `order` (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE order_catalog (id INT AUTO_INCREMENT NOT NULL, quantity INT NOT NULL, unit_price NUMERIC(10, 2) NOT NULL, order_id INT NOT NULL, catalog_id INT NOT NULL, UNIQUE INDEX uniq_order_catalog (order_id, catalog_id), INDEX IDX_ED569B0C8D9F6D38 (order_id), INDEX IDX_ED569B0CCC3C66FC (catalog_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE order_catalog ADD CONSTRAINT FK_ED569B0C8D9F6D38 FOREIGN KEY (order_id) REFERENCES `order` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE order_catalog ADD CONSTRAINT FK_ED569B0CCC3C66FC FOREIGN KEY (catalog_id) REFERENCES catalog (id)');
        $this->addSql('ALTER TABLE command_catalog DROP FOREIGN KEY `FK_F44661A433E1689A`');
        $this->addSql('ALTER TABLE command_catalog DROP FOREIGN KEY `FK_F44661A4CC3C66FC`');
        $this->addSql('DROP TABLE command');
        $this->addSql('DROP TABLE command_catalog');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE command (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE command_catalog (id INT AUTO_INCREMENT NOT NULL, quantity INT NOT NULL, unit_price NUMERIC(10, 2) NOT NULL, command_id INT NOT NULL, catalog_id INT NOT NULL, INDEX IDX_F44661A433E1689A (command_id), INDEX IDX_F44661A4CC3C66FC (catalog_id), UNIQUE INDEX uniq_command_catalog (command_id, catalog_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE command_catalog ADD CONSTRAINT `FK_F44661A433E1689A` FOREIGN KEY (command_id) REFERENCES command (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE command_catalog ADD CONSTRAINT `FK_F44661A4CC3C66FC` FOREIGN KEY (catalog_id) REFERENCES catalog (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE order_catalog DROP FOREIGN KEY FK_ED569B0C8D9F6D38');
        $this->addSql('ALTER TABLE order_catalog DROP FOREIGN KEY FK_ED569B0CCC3C66FC');
        $this->addSql('DROP TABLE `order`');
        $this->addSql('DROP TABLE order_catalog');
    }
}
