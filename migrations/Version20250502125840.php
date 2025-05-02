<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250502125840 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE download ADD COLUMN file_modification_date DATETIME DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE download ADD COLUMN file_name VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE downloadable_file ADD COLUMN creation_date DATETIME DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE downloadable_file ADD COLUMN file_modification_date DATETIME DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__download AS SELECT id, file_id, date, infos, ip FROM download
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE download
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE download (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, file_id INTEGER DEFAULT NULL, date DATETIME NOT NULL, infos CLOB DEFAULT NULL, ip VARCHAR(255) DEFAULT NULL, CONSTRAINT FK_781A827093CB796C FOREIGN KEY (file_id) REFERENCES downloadable_file (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO download (id, file_id, date, infos, ip) SELECT id, file_id, date, infos, ip FROM __temp__download
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE __temp__download
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_781A827093CB796C ON download (file_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__downloadable_file AS SELECT id, filename, token, name, lang, is_folder, sensible FROM downloadable_file
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE downloadable_file
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE downloadable_file (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, filename VARCHAR(255) DEFAULT NULL, token VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, lang VARCHAR(255) DEFAULT NULL, is_folder BOOLEAN DEFAULT NULL, sensible BOOLEAN DEFAULT NULL)
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO downloadable_file (id, filename, token, name, lang, is_folder, sensible) SELECT id, filename, token, name, lang, is_folder, sensible FROM __temp__downloadable_file
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE __temp__downloadable_file
        SQL);
    }
}
