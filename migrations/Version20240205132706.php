<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240205132706 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE downloadable_file ADD COLUMN sensible BOOLEAN DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__downloadable_file AS SELECT id, filename, token, name, lang, is_folder FROM downloadable_file');
        $this->addSql('DROP TABLE downloadable_file');
        $this->addSql('CREATE TABLE downloadable_file (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, filename VARCHAR(255) DEFAULT NULL, token VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, lang VARCHAR(255) DEFAULT NULL, is_folder BOOLEAN DEFAULT NULL)');
        $this->addSql('INSERT INTO downloadable_file (id, filename, token, name, lang, is_folder) SELECT id, filename, token, name, lang, is_folder FROM __temp__downloadable_file');
        $this->addSql('DROP TABLE __temp__downloadable_file');
    }
}
