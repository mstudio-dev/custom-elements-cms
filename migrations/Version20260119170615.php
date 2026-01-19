<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260119170615 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE news ADD COLUMN meta_title VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE news ADD COLUMN meta_description CLOB DEFAULT NULL');
        $this->addSql('ALTER TABLE page ADD COLUMN meta_title VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE page ADD COLUMN meta_description CLOB DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__news AS SELECT id, title, slug, content, image_ids, published_at, author, featured, status, created_at FROM news');
        $this->addSql('DROP TABLE news');
        $this->addSql('CREATE TABLE news (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, content CLOB NOT NULL, image_ids CLOB DEFAULT NULL, published_at DATETIME DEFAULT NULL, author VARCHAR(100) DEFAULT NULL, featured BOOLEAN NOT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL)');
        $this->addSql('INSERT INTO news (id, title, slug, content, image_ids, published_at, author, featured, status, created_at) SELECT id, title, slug, content, image_ids, published_at, author, featured, status, created_at FROM __temp__news');
        $this->addSql('DROP TABLE __temp__news');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1DD39950989D9B62 ON news (slug)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__page AS SELECT id, title, slug, published, created_at FROM page');
        $this->addSql('DROP TABLE page');
        $this->addSql('CREATE TABLE page (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, published BOOLEAN NOT NULL, created_at DATETIME NOT NULL)');
        $this->addSql('INSERT INTO page (id, title, slug, published, created_at) SELECT id, title, slug, published, created_at FROM __temp__page');
        $this->addSql('DROP TABLE __temp__page');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_140AB620989D9B62 ON page (slug)');
    }
}
