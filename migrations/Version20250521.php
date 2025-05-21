<?php

declare(strict_types=1);

namespace DoctrineMigrations;

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
        $this->addSql('ALTER TABLE team ALTER COLUMN external_id DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE team ALTER COLUMN external_id SET NOT NULL');
    }
}
