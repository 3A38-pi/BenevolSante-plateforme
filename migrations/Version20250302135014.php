<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250302135014 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE evaluation_response ADD question_id INT NOT NULL');
        $this->addSql('ALTER TABLE evaluation_response ADD CONSTRAINT FK_333572DD1E27F6BF FOREIGN KEY (question_id) REFERENCES question (id)');
        $this->addSql('CREATE INDEX IDX_333572DD1E27F6BF ON evaluation_response (question_id)');
        $this->addSql('ALTER TABLE response_evaluation DROP FOREIGN KEY FK_C0DB52951E27F6BF');
        $this->addSql('ALTER TABLE response_evaluation CHANGE answer answer LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE response_evaluation ADD CONSTRAINT FK_C0DB52951E27F6BF FOREIGN KEY (question_id) REFERENCES question (id)');
        $this->addSql('ALTER TABLE user ADD reset_token VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE evaluation_response DROP FOREIGN KEY FK_333572DD1E27F6BF');
        $this->addSql('DROP INDEX IDX_333572DD1E27F6BF ON evaluation_response');
        $this->addSql('ALTER TABLE evaluation_response DROP question_id');
        $this->addSql('ALTER TABLE response_evaluation DROP FOREIGN KEY FK_C0DB52951E27F6BF');
        $this->addSql('ALTER TABLE response_evaluation CHANGE answer answer VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE response_evaluation ADD CONSTRAINT FK_C0DB52951E27F6BF FOREIGN KEY (question_id) REFERENCES question (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user DROP reset_token');
    }
}
