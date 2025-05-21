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
        // Use a different SQL syntax that might be more compatible with the PostgreSQL version
        $this->addSql('ALTER TABLE team ALTER external_id DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE team ALTER external_id SET NOT NULL');
    }
}
