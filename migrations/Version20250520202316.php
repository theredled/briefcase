<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250520202316 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__downloadable_file AS SELECT id, filename, token, name, lang, is_folder, sensible, creation_date, file_modification_date FROM downloadable_file
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE downloadable_file
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE downloadable_file (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, filename VARCHAR(255) DEFAULT NULL, token VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, lang VARCHAR(255) DEFAULT NULL, is_folder BOOLEAN DEFAULT NULL, sensible BOOLEAN DEFAULT NULL, creation_date DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
            , file_modification_date DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
            )
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO downloadable_file (id, filename, token, name, lang, is_folder, sensible, creation_date, file_modification_date) SELECT id, filename, token, name, lang, is_folder, sensible, creation_date, file_modification_date FROM __temp__downloadable_file
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE __temp__downloadable_file
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__downloadable_file_downloadable_file AS SELECT downloadable_file_source, downloadable_file_target FROM downloadable_file_downloadable_file
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE downloadable_file_downloadable_file
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE downloadable_file_downloadable_file (downloadable_file_source INTEGER NOT NULL, downloadable_file_target INTEGER NOT NULL, PRIMARY KEY(downloadable_file_target, downloadable_file_source), CONSTRAINT FK_2EDB17F78B3E4208 FOREIGN KEY (downloadable_file_source) REFERENCES downloadable_file (id) ON UPDATE NO ACTION ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_2EDB17F792DB1287 FOREIGN KEY (downloadable_file_target) REFERENCES downloadable_file (id) ON UPDATE NO ACTION ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO downloadable_file_downloadable_file (downloadable_file_source, downloadable_file_target) SELECT downloadable_file_source, downloadable_file_target FROM __temp__downloadable_file_downloadable_file
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE __temp__downloadable_file_downloadable_file
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_2EDB17F792DB1287 ON downloadable_file_downloadable_file (downloadable_file_target)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_2EDB17F78B3E4208 ON downloadable_file_downloadable_file (downloadable_file_source)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__downloadable_file AS SELECT id, filename, creation_date, file_modification_date, token, name, lang, is_folder, sensible FROM downloadable_file
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE downloadable_file
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE downloadable_file (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, filename VARCHAR(255) DEFAULT NULL, creation_date DATETIME DEFAULT NULL, file_modification_date DATETIME DEFAULT NULL, token VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, lang VARCHAR(255) DEFAULT NULL, is_folder BOOLEAN DEFAULT NULL, sensible BOOLEAN DEFAULT NULL)
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO downloadable_file (id, filename, creation_date, file_modification_date, token, name, lang, is_folder, sensible) SELECT id, filename, creation_date, file_modification_date, token, name, lang, is_folder, sensible FROM __temp__downloadable_file
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE __temp__downloadable_file
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__downloadable_file_downloadable_file AS SELECT downloadable_file_target, downloadable_file_source FROM downloadable_file_downloadable_file
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE downloadable_file_downloadable_file
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE downloadable_file_downloadable_file (downloadable_file_target INTEGER NOT NULL, downloadable_file_source INTEGER NOT NULL, PRIMARY KEY(downloadable_file_source, downloadable_file_target), CONSTRAINT FK_2EDB17F792DB1287 FOREIGN KEY (downloadable_file_target) REFERENCES downloadable_file (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_2EDB17F78B3E4208 FOREIGN KEY (downloadable_file_source) REFERENCES downloadable_file (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO downloadable_file_downloadable_file (downloadable_file_target, downloadable_file_source) SELECT downloadable_file_target, downloadable_file_source FROM __temp__downloadable_file_downloadable_file
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE __temp__downloadable_file_downloadable_file
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_2EDB17F792DB1287 ON downloadable_file_downloadable_file (downloadable_file_target)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_2EDB17F78B3E4208 ON downloadable_file_downloadable_file (downloadable_file_source)
        SQL);
    }
}
