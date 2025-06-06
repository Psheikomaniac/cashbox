<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Make externalId nullable in Team entity
 */
final class Version20250521 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make externalId nullable in Team entity';
    }

    public function up(Schema $schema): void
    {
        // SQLite doesn't support ALTER COLUMN directly
        // We need to create a new table with the desired schema, copy data, drop old table, and rename new table
        $this->addSql('CREATE TABLE team_new (
            id CHAR(36) NOT NULL,
            name VARCHAR(255) NOT NULL,
            external_id VARCHAR(255) DEFAULT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('INSERT INTO team_new SELECT id, name, external_id, created_at, updated_at FROM team');
        $this->addSql('DROP TABLE team');
        $this->addSql('ALTER TABLE team_new RENAME TO team');
    }

    public function down(Schema $schema): void
    {
        // Reverse the process to make external_id NOT NULL again
        $this->addSql('CREATE TABLE team_new (
            id CHAR(36) NOT NULL,
            name VARCHAR(255) NOT NULL,
            external_id VARCHAR(255) NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('INSERT INTO team_new SELECT id, name, external_id, created_at, updated_at FROM team WHERE external_id IS NOT NULL');
        $this->addSql('DROP TABLE team');
        $this->addSql('ALTER TABLE team_new RENAME TO team');
    }
}
