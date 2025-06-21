<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Fix NULL values in users.roles column
 */
final class Version20250610000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update NULL values in users.roles column to empty JSON array';
    }

    public function up(Schema $schema): void
    {
        // Update all records where roles is NULL to set it to an empty JSON array
        $this->addSql("UPDATE users SET roles = '[]' WHERE roles IS NULL");
    }

    public function down(Schema $schema): void
    {
        // No down migration needed as we don't want to revert to NULL values
        $this->addSql('-- No down migration needed');
    }
}
