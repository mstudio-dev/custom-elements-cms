<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260118190115 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE page (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, published BOOLEAN NOT NULL, created_at DATETIME NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_140AB620989D9B62 ON page (slug)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__element AS SELECT id, data, sorting, published, created_at, element_type_id FROM element');
        $this->addSql('DROP TABLE element');
        $this->addSql('CREATE TABLE element (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, data CLOB NOT NULL, sorting INTEGER NOT NULL, published BOOLEAN NOT NULL, created_at DATETIME NOT NULL, element_type_id INTEGER NOT NULL, page_sorting INTEGER DEFAULT NULL, page_id INTEGER DEFAULT NULL, CONSTRAINT FK_41405E3932A7CCC7 FOREIGN KEY (element_type_id) REFERENCES element_type (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_41405E39C4663E4 FOREIGN KEY (page_id) REFERENCES page (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO element (id, data, sorting, published, created_at, element_type_id) SELECT id, data, sorting, published, created_at, element_type_id FROM __temp__element');
        $this->addSql('DROP TABLE __temp__element');
        $this->addSql('CREATE INDEX IDX_41405E3932A7CCC7 ON element (element_type_id)');
        $this->addSql('CREATE INDEX IDX_41405E39C4663E4 ON element (page_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE page');
        $this->addSql('CREATE TEMPORARY TABLE __temp__element AS SELECT id, data, sorting, published, created_at, element_type_id FROM element');
        $this->addSql('DROP TABLE element');
        $this->addSql('CREATE TABLE element (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, data CLOB NOT NULL, sorting INTEGER NOT NULL, published BOOLEAN NOT NULL, created_at DATETIME NOT NULL, element_type_id INTEGER NOT NULL, CONSTRAINT FK_41405E3932A7CCC7 FOREIGN KEY (element_type_id) REFERENCES element_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO element (id, data, sorting, published, created_at, element_type_id) SELECT id, data, sorting, published, created_at, element_type_id FROM __temp__element');
        $this->addSql('DROP TABLE __temp__element');
        $this->addSql('CREATE INDEX IDX_41405E3932A7CCC7 ON element (element_type_id)');
    }
}
