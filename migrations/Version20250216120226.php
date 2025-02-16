<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250216120226 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE condidat DROP FOREIGN KEY FK_3A8ACF2C4CC8505A');
        $this->addSql('DROP INDEX IDX_3A8ACF2C4CC8505A ON condidat');
        $this->addSql('ALTER TABLE condidat DROP offre_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE condidat ADD offre_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE condidat ADD CONSTRAINT FK_3A8ACF2C4CC8505A FOREIGN KEY (offre_id) REFERENCES offre (id)');
        $this->addSql('CREATE INDEX IDX_3A8ACF2C4CC8505A ON condidat (offre_id)');
    }
}
