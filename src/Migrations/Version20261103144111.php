<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261103144111 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE command (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE command_catalog (id INT AUTO_INCREMENT NOT NULL, quantity INT NOT NULL, unit_price NUMERIC(10, 2) NOT NULL, command_id INT NOT NULL, catalog_id INT NOT NULL, UNIQUE INDEX uniq_command_catalog (command_id, catalog_id), INDEX IDX_F44661A433E1689A (command_id), INDEX IDX_F44661A4CC3C66FC (catalog_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE command_catalog ADD CONSTRAINT FK_F44661A433E1689A FOREIGN KEY (command_id) REFERENCES command (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE command_catalog ADD CONSTRAINT FK_F44661A4CC3C66FC FOREIGN KEY (catalog_id) REFERENCES catalog (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE command_catalog DROP FOREIGN KEY FK_F44661A433E1689A');
        $this->addSql('ALTER TABLE command_catalog DROP FOREIGN KEY FK_F44661A4CC3C66FC');
        $this->addSql('DROP TABLE command');
        $this->addSql('DROP TABLE command_catalog');
    }
}
