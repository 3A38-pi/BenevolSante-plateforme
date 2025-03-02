<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250225085050 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, commentaire_id INT DEFAULT NULL, message VARCHAR(500) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_BF5476CAA76ED395 (user_id), UNIQUE INDEX UNIQ_BF5476CABA9CD190 (commentaire_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CABA9CD190 FOREIGN KEY (commentaire_id) REFERENCES commentaire (id)');
        $this->addSql('ALTER TABLE response_evaluation DROP FOREIGN KEY FK_C0DB52951E27F6BF');
        $this->addSql('DROP TABLE response_evaluation');
        $this->addSql('ALTER TABLE article ADD created_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE commentaire ADD created_at DATETIME DEFAULT NULL, ADD etat VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE demande_dons ADD chat_actif TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE evaluation ADD question VARCHAR(255) NOT NULL, ADD options JSON NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE evaluation_response ADD question_id INT NOT NULL');
        $this->addSql('ALTER TABLE evaluation_response ADD CONSTRAINT FK_333572DD1E27F6BF FOREIGN KEY (question_id) REFERENCES question (id)');
        $this->addSql('CREATE INDEX IDX_333572DD1E27F6BF ON evaluation_response (question_id)');
        $this->addSql('ALTER TABLE response DROP FOREIGN KEY FK_3E7B0BFB1E27F6BF');
        $this->addSql('DROP INDEX IDX_3E7B0BFB1E27F6BF ON response');
        $this->addSql('ALTER TABLE response DROP question_id, DROP answer, DROP rating');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE response_evaluation (id INT AUTO_INCREMENT NOT NULL, question_id INT NOT NULL, answer VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, rating DOUBLE PRECISION DEFAULT NULL, INDEX IDX_C0DB52951E27F6BF (question_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE response_evaluation ADD CONSTRAINT FK_C0DB52951E27F6BF FOREIGN KEY (question_id) REFERENCES question (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAA76ED395');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CABA9CD190');
        $this->addSql('DROP TABLE notification');
        $this->addSql('ALTER TABLE article DROP created_at');
        $this->addSql('ALTER TABLE commentaire DROP created_at, DROP etat');
        $this->addSql('ALTER TABLE demande_dons DROP chat_actif');
        $this->addSql('ALTER TABLE evaluation DROP question, DROP options');
        $this->addSql('ALTER TABLE evaluation_response DROP FOREIGN KEY FK_333572DD1E27F6BF');
        $this->addSql('DROP INDEX IDX_333572DD1E27F6BF ON evaluation_response');
        $this->addSql('ALTER TABLE evaluation_response DROP question_id');
        $this->addSql('ALTER TABLE response ADD question_id INT NOT NULL, ADD answer VARCHAR(255) NOT NULL, ADD rating DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE response ADD CONSTRAINT FK_3E7B0BFB1E27F6BF FOREIGN KEY (question_id) REFERENCES question (id)');
        $this->addSql('CREATE INDEX IDX_3E7B0BFB1E27F6BF ON response (question_id)');
    }
}
