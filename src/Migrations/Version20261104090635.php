<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261104090635 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE catalog ADD category VARCHAR(50) DEFAULT NULL, ADD level VARCHAR(20) DEFAULT NULL, ADD tagline VARCHAR(255) DEFAULT NULL, ADD soldering_required TINYINT DEFAULT NULL, ADD specs JSON DEFAULT NULL, ADD compatibility JSON DEFAULT NULL, ADD box_contents JSON DEFAULT NULL, ADD accessories JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE catalog DROP category, DROP level, DROP tagline, DROP soldering_required, DROP specs, DROP compatibility, DROP box_contents, DROP accessories');
    }
}
