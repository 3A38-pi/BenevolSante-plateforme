<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250225104209 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE response_evaluation DROP FOREIGN KEY FK_C0DB52951E27F6BF');
        $this->addSql('ALTER TABLE response_evaluation DROP response_text, DROP created_at');
        $this->addSql('ALTER TABLE response_evaluation ADD CONSTRAINT FK_C0DB52951E27F6BF FOREIGN KEY (question_id) REFERENCES question (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE response_evaluation DROP FOREIGN KEY FK_C0DB52951E27F6BF');
        $this->addSql('ALTER TABLE response_evaluation ADD response_text LONGTEXT DEFAULT NULL, ADD created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE response_evaluation ADD CONSTRAINT FK_C0DB52951E27F6BF FOREIGN KEY (question_id) REFERENCES question (id) ON UPDATE CASCADE ON DELETE CASCADE');
    }
}
