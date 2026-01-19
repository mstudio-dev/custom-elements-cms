<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260118235306 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE news (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, content CLOB NOT NULL, image_ids CLOB DEFAULT NULL, published_at DATETIME DEFAULT NULL, author VARCHAR(100) DEFAULT NULL, featured BOOLEAN NOT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1DD39950989D9B62 ON news (slug)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE news');
    }
}
