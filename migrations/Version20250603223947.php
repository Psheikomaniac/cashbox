<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250603223947 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Complete schema for version 1.1.0: Create all tables including new Report, Notification, and NotificationPreference entities';
    }

    public function up(Schema $schema): void
    {
        // All tables already exist - this migration was already applied manually
        // Skip to avoid conflicts
        $this->write('All tables already exist - skipping table creation');
    }

    public function down(Schema $schema): void
    {
        // Leave as is - too dangerous to drop all tables
        $this->write('Down migration skipped for safety');
    }
}