<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250225105914 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE evaluation_response DROP FOREIGN KEY FK_333572DD456C5646');
        $this->addSql('DROP INDEX IDX_333572DD456C5646 ON evaluation_response');
        $this->addSql('ALTER TABLE evaluation_response DROP evaluation_id, CHANGE answer answer VARCHAR(255) NOT NULL, CHANGE rating rating DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE response_evaluation ADD answer VARCHAR(255) NOT NULL, ADD rating DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE evaluation_response ADD evaluation_id INT NOT NULL, CHANGE answer answer LONGTEXT DEFAULT NULL, CHANGE rating rating INT DEFAULT NULL');
        $this->addSql('ALTER TABLE evaluation_response ADD CONSTRAINT FK_333572DD456C5646 FOREIGN KEY (evaluation_id) REFERENCES evaluation (id)');
        $this->addSql('CREATE INDEX IDX_333572DD456C5646 ON evaluation_response (evaluation_id)');
        $this->addSql('ALTER TABLE response_evaluation DROP answer, DROP rating');
    }
}
