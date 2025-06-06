<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Version 1.2.0 Contribution Management - Modern Domain Model Implementation
 */
final class Version20241206000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Version 1.2.0: Enhanced contribution management with modern domain model, Money value object, and RecurrencePattern enum';
    }

    public function up(Schema $schema): void
    {
        // SQLite-compatible updates - just add indexes and constraints since tables already exist
        // Note: SQLite doesn't support ALTER COLUMN, so we work with existing structure

        // Create enhanced indexes for performance (if they don't exist)
        try {
            $this->addSql('CREATE INDEX IF NOT EXISTS idx_contribution_team_user_active ON contribution (team_user_id, active)');
            $this->addSql('CREATE INDEX IF NOT EXISTS idx_contribution_due_date ON contribution (due_date)');
            $this->addSql('CREATE INDEX IF NOT EXISTS idx_contribution_paid_at ON contribution (paid_at)');

            $this->addSql('CREATE INDEX IF NOT EXISTS idx_contribution_type_active ON contribution_type (active)');
            $this->addSql('CREATE INDEX IF NOT EXISTS idx_contribution_type_recurring ON contribution_type (recurring, active)');

            $this->addSql('CREATE INDEX IF NOT EXISTS idx_contribution_template_team_active ON contribution_template (team_id, active)');

            $this->addSql('CREATE INDEX IF NOT EXISTS idx_contribution_payment_contribution ON contribution_payment (contribution_id)');
            $this->addSql('CREATE INDEX IF NOT EXISTS idx_contribution_payment_created_at ON contribution_payment (created_at)');
        } catch (\Exception $e) {
            // Indexes might already exist, continue
        }

        // Note: For SQLite, we can't add CHECK constraints to existing tables easily
        // The constraints will be enforced at the application level through our domain models
        // Future migrations can recreate tables with proper constraints if needed
    }

    public function down(Schema $schema): void
    {
        // Remove enhanced indexes for SQLite
        $this->addSql('DROP INDEX IF EXISTS idx_contribution_team_user_active');
        $this->addSql('DROP INDEX IF EXISTS idx_contribution_due_date');
        $this->addSql('DROP INDEX IF EXISTS idx_contribution_paid_at');
        $this->addSql('DROP INDEX IF EXISTS idx_contribution_type_active');
        $this->addSql('DROP INDEX IF EXISTS idx_contribution_type_recurring');
        $this->addSql('DROP INDEX IF EXISTS idx_contribution_template_team_active');
        $this->addSql('DROP INDEX IF EXISTS idx_contribution_payment_contribution');
        $this->addSql('DROP INDEX IF EXISTS idx_contribution_payment_created_at');
    }
}
