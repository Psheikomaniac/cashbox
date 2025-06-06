<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250523064223 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create tables for contribution management: contribution, contribution_payment, contribution_template, contribution_type';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE contribution (id CHAR(36) NOT NULL --(DC2Type:uuid)
            , team_user_id CHAR(36) NOT NULL --(DC2Type:uuid)
            , type_id CHAR(36) NOT NULL --(DC2Type:uuid)
            , description VARCHAR(255) NOT NULL, amount INTEGER NOT NULL, currency VARCHAR(3) NOT NULL, due_date VARCHAR(255) NOT NULL, paid_at VARCHAR(255) DEFAULT NULL, active BOOLEAN NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
            , updated_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
            , PRIMARY KEY(id), CONSTRAINT FK_EA351E155817A2A2 FOREIGN KEY (team_user_id) REFERENCES team_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_EA351E15C54C8C93 FOREIGN KEY (type_id) REFERENCES contribution_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_EA351E155817A2A2 ON contribution (team_user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_EA351E15C54C8C93 ON contribution (type_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE contribution_payment (id CHAR(36) NOT NULL --(DC2Type:uuid)
            , contribution_id CHAR(36) NOT NULL --(DC2Type:uuid)
            , amount INTEGER NOT NULL, currency VARCHAR(3) NOT NULL, payment_method VARCHAR(255) DEFAULT NULL, reference VARCHAR(255) DEFAULT NULL, notes CLOB DEFAULT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
            , updated_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
            , PRIMARY KEY(id), CONSTRAINT FK_2C09F4CCFE5E5FBD FOREIGN KEY (contribution_id) REFERENCES contribution (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_2C09F4CCFE5E5FBD ON contribution_payment (contribution_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE contribution_template (id CHAR(36) NOT NULL --(DC2Type:uuid)
            , team_id CHAR(36) NOT NULL --(DC2Type:uuid)
            , name VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL, amount INTEGER NOT NULL, currency VARCHAR(3) NOT NULL, recurring BOOLEAN NOT NULL, recurrence_pattern VARCHAR(255) DEFAULT NULL, due_days INTEGER DEFAULT NULL, active BOOLEAN NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
            , updated_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
            , PRIMARY KEY(id), CONSTRAINT FK_7B42CCD5296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_7B42CCD5296CD8AE ON contribution_template (team_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE contribution_type (id CHAR(36) NOT NULL --(DC2Type:uuid)
            , name VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL, recurring BOOLEAN NOT NULL, recurrence_pattern VARCHAR(255) DEFAULT NULL, active BOOLEAN NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
            , updated_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
            , PRIMARY KEY(id))
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP TABLE contribution
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE contribution_payment
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE contribution_template
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE contribution_type
        SQL);
    }
}
