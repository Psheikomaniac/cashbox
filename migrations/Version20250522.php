<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Make external_id nullable in Team entity
 */
final class Version20250522 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make external_id nullable in Team entity';
    }

    public function up(Schema $schema): void
    {
        // This migration is no longer needed as Version20250521 now handles this change correctly
        // for SQLite. Keeping this empty to avoid conflicts.
    }

    public function down(Schema $schema): void
    {
        // No action needed as Version20250521 handles the reversion
    }
}
