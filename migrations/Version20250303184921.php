<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250303184921 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE response_evaluation (id INT AUTO_INCREMENT NOT NULL, question_id INT NOT NULL, answer LONGTEXT NOT NULL, rating INT DEFAULT NULL, INDEX IDX_C0DB52951E27F6BF (question_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE response_evaluation ADD CONSTRAINT FK_C0DB52951E27F6BF FOREIGN KEY (question_id) REFERENCES question (id)');
        $this->addSql('ALTER TABLE evaluation DROP question, DROP options');
        $this->addSql('ALTER TABLE response ADD question_id INT NOT NULL, ADD answer VARCHAR(255) NOT NULL, ADD rating INT DEFAULT NULL');
        $this->addSql('ALTER TABLE response ADD CONSTRAINT FK_3E7B0BFB1E27F6BF FOREIGN KEY (question_id) REFERENCES question (id)');
        $this->addSql('CREATE INDEX IDX_3E7B0BFB1E27F6BF ON response (question_id)');
        $this->addSql('ALTER TABLE user ADD google_id VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE response_evaluation DROP FOREIGN KEY FK_C0DB52951E27F6BF');
        $this->addSql('DROP TABLE response_evaluation');
        $this->addSql('ALTER TABLE evaluation ADD question VARCHAR(255) NOT NULL, ADD options JSON NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE response DROP FOREIGN KEY FK_3E7B0BFB1E27F6BF');
        $this->addSql('DROP INDEX IDX_3E7B0BFB1E27F6BF ON response');
        $this->addSql('ALTER TABLE response DROP question_id, DROP answer, DROP rating');
        $this->addSql('ALTER TABLE user DROP google_id');
    }
}
