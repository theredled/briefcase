<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260205123140 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE briefcase (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, color VARCHAR(255) NULL, token VARCHAR(255) NOT NULL, user_id INTEGER DEFAULT NULL, CONSTRAINT FK_73C0784BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_73C0784BA76ED395 ON briefcase (user_id)');
        $this->addSql('DROP TABLE video');
        $this->addSql('CREATE TEMPORARY TABLE __temp__download AS SELECT id, file_id, date, infos, ip, file_modification_date, file_name FROM download');
        $this->addSql('DROP TABLE download');
        $this->addSql('CREATE TABLE download (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, file_id INTEGER DEFAULT NULL, date DATETIME NOT NULL, infos CLOB DEFAULT NULL, ip VARCHAR(255) DEFAULT NULL, file_modification_date DATETIME DEFAULT NULL, file_name VARCHAR(255) DEFAULT NULL, document_id INTEGER DEFAULT NULL, CONSTRAINT FK_781A8270C33F7837 FOREIGN KEY (document_id) REFERENCES downloadable_file (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO download (id, file_id, date, infos, ip, file_modification_date, file_name) SELECT id, file_id, date, infos, ip, file_modification_date, file_name FROM __temp__download');
        $this->addSql('DROP TABLE __temp__download');
        $this->addSql('CREATE INDEX IDX_781A8270C33F7837 ON download (document_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__downloadable_file AS SELECT id, filename, token, name, lang, is_folder, sensible, creation_date, file_modification_date FROM downloadable_file');
        $this->addSql('DROP TABLE downloadable_file');
        $this->addSql('CREATE TABLE downloadable_file (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, filename VARCHAR(255) DEFAULT NULL, token VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, lang VARCHAR(255) DEFAULT NULL, is_folder BOOLEAN DEFAULT NULL, sensible BOOLEAN DEFAULT NULL, creation_date DATETIME DEFAULT NULL, file_modification_date DATETIME DEFAULT NULL, briefcase_id INTEGER DEFAULT NULL, CONSTRAINT FK_F90A22BF660C962C FOREIGN KEY (briefcase_id) REFERENCES briefcase (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO downloadable_file (id, filename, token, name, lang, is_folder, sensible, creation_date, file_modification_date) SELECT id, filename, token, name, lang, is_folder, sensible, creation_date, file_modification_date FROM __temp__downloadable_file');
        $this->addSql('DROP TABLE __temp__downloadable_file');
        $this->addSql('CREATE INDEX IDX_F90A22BF660C962C ON downloadable_file (briefcase_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE video (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, url CLOB NOT NULL COLLATE "BINARY", title VARCHAR(255) NOT NULL COLLATE "BINARY")');
        $this->addSql('DROP TABLE briefcase');
        $this->addSql('CREATE TEMPORARY TABLE __temp__download AS SELECT id, date, file_modification_date, file_name, file_id, infos, ip FROM download');
        $this->addSql('DROP TABLE download');
        $this->addSql('CREATE TABLE download (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, date DATETIME NOT NULL, file_modification_date DATETIME DEFAULT NULL, file_name VARCHAR(255) DEFAULT NULL, file_id INTEGER DEFAULT NULL, infos CLOB DEFAULT NULL, ip VARCHAR(255) DEFAULT NULL, CONSTRAINT FK_781A827093CB796C FOREIGN KEY (file_id) REFERENCES downloadable_file (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO download (id, date, file_modification_date, file_name, file_id, infos, ip) SELECT id, date, file_modification_date, file_name, file_id, infos, ip FROM __temp__download');
        $this->addSql('DROP TABLE __temp__download');
        $this->addSql('CREATE INDEX IDX_781A827093CB796C ON download (file_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__downloadable_file AS SELECT id, filename, creation_date, file_modification_date, token, name, lang, is_folder, sensible FROM downloadable_file');
        $this->addSql('DROP TABLE downloadable_file');
        $this->addSql('CREATE TABLE downloadable_file (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, filename VARCHAR(255) DEFAULT NULL, creation_date DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , file_modification_date DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , token VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, lang VARCHAR(255) DEFAULT NULL, is_folder BOOLEAN DEFAULT NULL, sensible BOOLEAN DEFAULT NULL)');
        $this->addSql('INSERT INTO downloadable_file (id, filename, creation_date, file_modification_date, token, name, lang, is_folder, sensible) SELECT id, filename, creation_date, file_modification_date, token, name, lang, is_folder, sensible FROM __temp__downloadable_file');
        $this->addSql('DROP TABLE __temp__downloadable_file');
    }
}
