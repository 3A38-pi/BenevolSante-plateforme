<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250215115827 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE demande_dons (id INT AUTO_INCREMENT NOT NULL, beneficiaire_id INT NOT NULL, dons_id INT NOT NULL, statut VARCHAR(20) NOT NULL, date_demande DATE NOT NULL, INDEX IDX_835C272F5AF81F68 (beneficiaire_id), INDEX IDX_835C272FDDBFD07B (dons_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE dons (id INT AUTO_INCREMENT NOT NULL, donneur_id INT NOT NULL, titre VARCHAR(100) NOT NULL, description VARCHAR(255) NOT NULL, date_creation DATE NOT NULL, valide TINYINT(1) NOT NULL, image_url VARCHAR(255) DEFAULT NULL, INDEX IDX_E4F955FA9789825B (donneur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messagerie (id INT AUTO_INCREMENT NOT NULL, expediteur_id INT NOT NULL, destinataire_id INT NOT NULL, demande_don_id INT NOT NULL, contenu LONGTEXT NOT NULL, date_envoi DATETIME NOT NULL, INDEX IDX_14E8F60C10335F61 (expediteur_id), INDEX IDX_14E8F60CA4F84F6E (destinataire_id), INDEX IDX_14E8F60C3978D68E (demande_don_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rendez_vous (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, telephone VARCHAR(255) NOT NULL, date DATETIME NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', nom VARCHAR(100) DEFAULT NULL, prenom VARCHAR(100) DEFAULT NULL, etat_compte VARCHAR(20) DEFAULT \'verrouillé\' NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE demande_dons ADD CONSTRAINT FK_835C272F5AF81F68 FOREIGN KEY (beneficiaire_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE demande_dons ADD CONSTRAINT FK_835C272FDDBFD07B FOREIGN KEY (dons_id) REFERENCES dons (id)');
        $this->addSql('ALTER TABLE dons ADD CONSTRAINT FK_E4F955FA9789825B FOREIGN KEY (donneur_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE messagerie ADD CONSTRAINT FK_14E8F60C10335F61 FOREIGN KEY (expediteur_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE messagerie ADD CONSTRAINT FK_14E8F60CA4F84F6E FOREIGN KEY (destinataire_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE messagerie ADD CONSTRAINT FK_14E8F60C3978D68E FOREIGN KEY (demande_don_id) REFERENCES demande_dons (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE demande_dons DROP FOREIGN KEY FK_835C272F5AF81F68');
        $this->addSql('ALTER TABLE demande_dons DROP FOREIGN KEY FK_835C272FDDBFD07B');
        $this->addSql('ALTER TABLE dons DROP FOREIGN KEY FK_E4F955FA9789825B');
        $this->addSql('ALTER TABLE messagerie DROP FOREIGN KEY FK_14E8F60C10335F61');
        $this->addSql('ALTER TABLE messagerie DROP FOREIGN KEY FK_14E8F60CA4F84F6E');
        $this->addSql('ALTER TABLE messagerie DROP FOREIGN KEY FK_14E8F60C3978D68E');
        $this->addSql('DROP TABLE demande_dons');
        $this->addSql('DROP TABLE dons');
        $this->addSql('DROP TABLE messagerie');
        $this->addSql('DROP TABLE rendez_vous');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
