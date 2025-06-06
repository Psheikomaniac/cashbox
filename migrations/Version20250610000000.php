<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add indexes to frequently queried fields in Penalty entity
 */
final class Version20250610000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add indexes to frequently queried fields in Penalty entity';
    }

    public function up(Schema $schema): void
    {
        // Create indexes for frequently queried fields
        $this->addSql('CREATE INDEX idx_penalty_team_user ON penalties (team_user_id)');
        $this->addSql('CREATE INDEX idx_penalty_paid_at ON penalties (paid_at)');
        $this->addSql('CREATE INDEX idx_penalty_archived ON penalties (archived)');
        $this->addSql('CREATE INDEX idx_penalty_created_at ON penalties (created_at)');
        $this->addSql('CREATE INDEX idx_penalty_type ON penalties (type_id)');

        // Create composite indexes for common query patterns
        $this->addSql('CREATE INDEX idx_penalty_team_user_paid_at ON penalties (team_user_id, paid_at)');
        $this->addSql('CREATE INDEX idx_penalty_team_user_archived ON penalties (team_user_id, archived)');
        $this->addSql('CREATE INDEX idx_penalty_archived_paid_at ON penalties (archived, paid_at)');
    }

    public function down(Schema $schema): void
    {
        // Remove indexes
        $this->addSql('DROP INDEX IF EXISTS idx_penalty_team_user');
        $this->addSql('DROP INDEX IF EXISTS idx_penalty_paid_at');
        $this->addSql('DROP INDEX IF EXISTS idx_penalty_archived');
        $this->addSql('DROP INDEX IF EXISTS idx_penalty_created_at');
        $this->addSql('DROP INDEX IF EXISTS idx_penalty_type');

        $this->addSql('DROP INDEX IF EXISTS idx_penalty_team_user_paid_at');
        $this->addSql('DROP INDEX IF EXISTS idx_penalty_team_user_archived');
        $this->addSql('DROP INDEX IF EXISTS idx_penalty_archived_paid_at');
    }
}
