<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250506131930 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $project_dir = __DIR__.'/..';

        $qb = $this->connection->createQueryBuilder()
            ->from('downloadable_file', 'f')
            ->select('f.filename, f.id, f.token')
            ->andWhere('f.filename IS NOT NULL AND f.is_folder = 0');

        foreach ($qb->executeQuery()->fetchAllAssociative() as $row) {
            $path = $project_dir.'/var/downloadable_files/'.$row['filename'];

            if (!is_file($path)) {
                echo $row['filename']." not exists\n";
                continue;
            }

            $this->addSql(<<<'SQL'
                    UPDATE downloadable_file SET file_modification_date = :ts WHERE id = :id;
                SQL, [
                'ts' => date('Y-m-d H:i:s', filemtime($path)),
                'id' => $row['id'],
            ]);
        }

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
