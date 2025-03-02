<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250301000540 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification DROP INDEX UNIQ_BF5476CAED486BD4, ADD INDEX IDX_BF5476CAED486BD4 (demande_dons_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification DROP INDEX IDX_BF5476CAED486BD4, ADD UNIQUE INDEX UNIQ_BF5476CAED486BD4 (demande_dons_id)');
    }
}
