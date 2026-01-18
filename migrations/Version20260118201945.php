<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260118201945 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE media (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(50) NOT NULL, mime_type VARCHAR(100) DEFAULT NULL, size INTEGER DEFAULT NULL, path VARCHAR(500) DEFAULT NULL, sorting INTEGER NOT NULL, description CLOB DEFAULT NULL, alt VARCHAR(255) DEFAULT NULL, uploaded_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, parent_id INTEGER DEFAULT NULL, CONSTRAINT FK_6A2CA10C727ACA70 FOREIGN KEY (parent_id) REFERENCES media (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_6A2CA10C727ACA70 ON media (parent_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE media');
    }
}
