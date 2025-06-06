<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add indexes to frequently queried fields in Payment entity
 */
final class Version20250610000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add indexes to frequently queried fields in Payment entity';
    }

    public function up(Schema $schema): void
    {
        // Create indexes for frequently queried fields
        $this->addSql('CREATE INDEX idx_payment_team_user ON payment (team_user_id)');
        $this->addSql('CREATE INDEX idx_payment_type ON payment (type)');
        $this->addSql('CREATE INDEX idx_payment_created_at ON payment (created_at)');

        // Create composite indexes for common query patterns
        $this->addSql('CREATE INDEX idx_payment_team_user_type ON payment (team_user_id, type)');
        $this->addSql('CREATE INDEX idx_payment_team_user_created_at ON payment (team_user_id, created_at)');
    }

    public function down(Schema $schema): void
    {
        // Remove indexes
        $this->addSql('DROP INDEX IF EXISTS idx_payment_team_user');
        $this->addSql('DROP INDEX IF EXISTS idx_payment_type');
        $this->addSql('DROP INDEX IF EXISTS idx_payment_created_at');

        $this->addSql('DROP INDEX IF EXISTS idx_payment_team_user_type');
        $this->addSql('DROP INDEX IF EXISTS idx_payment_team_user_created_at');
    }
}
