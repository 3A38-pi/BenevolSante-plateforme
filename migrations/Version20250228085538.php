<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250228085538 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification ADD demande_dons_id INT DEFAULT NULL, ADD type VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAED486BD4 FOREIGN KEY (demande_dons_id) REFERENCES demande_dons (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BF5476CAED486BD4 ON notification (demande_dons_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAED486BD4');
        $this->addSql('DROP INDEX UNIQ_BF5476CAED486BD4 ON notification');
        $this->addSql('ALTER TABLE notification DROP demande_dons_id, DROP type');
    }
}
