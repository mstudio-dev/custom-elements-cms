<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260110153051 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE element (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, data CLOB NOT NULL, sorting INTEGER NOT NULL, published BOOLEAN NOT NULL, created_at DATETIME NOT NULL, element_type_id INTEGER NOT NULL, CONSTRAINT FK_41405E3932A7CCC7 FOREIGN KEY (element_type_id) REFERENCES element_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_41405E3932A7CCC7 ON element (element_type_id)');
        $this->addSql('CREATE TABLE element_type (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, label VARCHAR(255) NOT NULL, fields CLOB NOT NULL, template CLOB DEFAULT NULL)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE element');
        $this->addSql('DROP TABLE element_type');
    }
}
