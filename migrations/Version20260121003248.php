<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260121003248 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__page AS SELECT id, title, slug, published, created_at, meta_title, meta_description FROM page');
        $this->addSql('DROP TABLE page');
        $this->addSql('CREATE TABLE page (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, published BOOLEAN NOT NULL, created_at DATETIME NOT NULL, meta_title VARCHAR(255) DEFAULT NULL, meta_description CLOB DEFAULT NULL, sorting INTEGER NOT NULL, show_in_navigation BOOLEAN NOT NULL, content CLOB DEFAULT NULL, parent_id INTEGER DEFAULT NULL, CONSTRAINT FK_140AB620727ACA70 FOREIGN KEY (parent_id) REFERENCES page (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO page (id, title, slug, published, created_at, meta_title, meta_description) SELECT id, title, slug, published, created_at, meta_title, meta_description FROM __temp__page');
        $this->addSql('DROP TABLE __temp__page');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_140AB620989D9B62 ON page (slug)');
        $this->addSql('CREATE INDEX IDX_140AB620727ACA70 ON page (parent_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__page AS SELECT id, title, slug, published, meta_title, meta_description, created_at FROM page');
        $this->addSql('DROP TABLE page');
        $this->addSql('CREATE TABLE page (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, published BOOLEAN NOT NULL, meta_title VARCHAR(255) DEFAULT NULL, meta_description CLOB DEFAULT NULL, created_at DATETIME NOT NULL)');
        $this->addSql('INSERT INTO page (id, title, slug, published, meta_title, meta_description, created_at) SELECT id, title, slug, published, meta_title, meta_description, created_at FROM __temp__page');
        $this->addSql('DROP TABLE __temp__page');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_140AB620989D9B62 ON page (slug)');
    }
}
