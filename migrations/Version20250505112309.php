<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250505112309 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE downloadable_file_downloadable_file (downloadable_file_source INTEGER NOT NULL, downloadable_file_target INTEGER NOT NULL, PRIMARY KEY(downloadable_file_source, downloadable_file_target), CONSTRAINT FK_2EDB17F78B3E4208 FOREIGN KEY (downloadable_file_source) REFERENCES downloadable_file (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_2EDB17F792DB1287 FOREIGN KEY (downloadable_file_target) REFERENCES downloadable_file (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_2EDB17F78B3E4208 ON downloadable_file_downloadable_file (downloadable_file_source)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_2EDB17F792DB1287 ON downloadable_file_downloadable_file (downloadable_file_target)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP TABLE downloadable_file_downloadable_file
        SQL);
    }
}
