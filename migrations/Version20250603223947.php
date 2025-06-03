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
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE contribution (id CHAR(36) NOT NULL --(DC2Type:uuid)
            , team_user_id CHAR(36) NOT NULL --(DC2Type:uuid)
            , type_id CHAR(36) NOT NULL --(DC2Type:uuid)
            , description VARCHAR(255) NOT NULL, amount INTEGER NOT NULL, currency VARCHAR(3) NOT NULL, due_date DATETIME NOT NULL --(DC2Type:datetime_immutable)
            , paid_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
            , active BOOLEAN NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
            , updated_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
            , PRIMARY KEY(id), CONSTRAINT FK_EA351E155817A2A2 FOREIGN KEY (team_user_id) REFERENCES team_users (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_EA351E15C54C8C93 FOREIGN KEY (type_id) REFERENCES contribution_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
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
            , PRIMARY KEY(id), CONSTRAINT FK_7B42CCD5296CD8AE FOREIGN KEY (team_id) REFERENCES teams (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
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
        $this->addSql(<<<'SQL'
            CREATE TABLE notification_preferences (id CHAR(36) NOT NULL --(DC2Type:uuid)
            , user_id CHAR(36) NOT NULL --(DC2Type:uuid)
            , notification_type VARCHAR(255) NOT NULL, email_enabled BOOLEAN NOT NULL, in_app_enabled BOOLEAN NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
            , updated_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
            , PRIMARY KEY(id), CONSTRAINT FK_3CAA95B4A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3CAA95B4A76ED395 ON notification_preferences (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_3CAA95B4A76ED39534E21C13 ON notification_preferences (user_id, notification_type)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE notifications (id CHAR(36) NOT NULL --(DC2Type:uuid)
            , user_id CHAR(36) NOT NULL --(DC2Type:uuid)
            , type VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, message CLOB NOT NULL, data CLOB DEFAULT NULL --(DC2Type:json)
            , read BOOLEAN NOT NULL, read_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
            , created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
            , PRIMARY KEY(id), CONSTRAINT FK_6000B0D3A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6000B0D3A76ED395 ON notifications (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_user_read ON notifications (user_id, read)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_created_at ON notifications (created_at)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE payment (id CHAR(36) NOT NULL --(DC2Type:uuid)
            , team_user_id CHAR(36) NOT NULL --(DC2Type:uuid)
            , amount INTEGER NOT NULL, currency VARCHAR(3) NOT NULL, type VARCHAR(30) NOT NULL, description VARCHAR(255) DEFAULT NULL, reference VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
            , updated_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
            , PRIMARY KEY(id), CONSTRAINT FK_6D28840D5817A2A2 FOREIGN KEY (team_user_id) REFERENCES team_users (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6D28840D5817A2A2 ON payment (team_user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE penalties (id CHAR(36) NOT NULL --(DC2Type:uuid)
            , team_user_id CHAR(36) NOT NULL --(DC2Type:uuid)
            , type_id CHAR(36) NOT NULL --(DC2Type:uuid)
            , reason VARCHAR(255) NOT NULL, amount INTEGER NOT NULL, currency VARCHAR(3) NOT NULL, archived BOOLEAN NOT NULL, paid_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
            , created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
            , updated_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
            , PRIMARY KEY(id), CONSTRAINT FK_B89F70AF5817A2A2 FOREIGN KEY (team_user_id) REFERENCES team_users (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_B89F70AFC54C8C93 FOREIGN KEY (type_id) REFERENCES penalty_types (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_B89F70AF5817A2A2 ON penalties (team_user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_B89F70AFC54C8C93 ON penalties (type_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE penalty_types (id CHAR(36) NOT NULL --(DC2Type:uuid)
            , name VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL, type VARCHAR(30) NOT NULL, default_amount INTEGER NOT NULL, active BOOLEAN NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
            , updated_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
            , PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE reports (id CHAR(36) NOT NULL --(DC2Type:uuid)
            , created_by_id CHAR(36) NOT NULL --(DC2Type:uuid)
            , name VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, parameters CLOB NOT NULL --(DC2Type:json)
            , result CLOB DEFAULT NULL --(DC2Type:json)
            , scheduled BOOLEAN NOT NULL, cron_expression VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
            , updated_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
            , PRIMARY KEY(id), CONSTRAINT FK_F11FA745B03A8386 FOREIGN KEY (created_by_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_F11FA745B03A8386 ON reports (created_by_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE team_users (id CHAR(36) NOT NULL --(DC2Type:uuid)
            , team_id CHAR(36) NOT NULL --(DC2Type:uuid)
            , user_id CHAR(36) NOT NULL --(DC2Type:uuid)
            , roles CLOB NOT NULL --(DC2Type:json)
            , active BOOLEAN NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
            , updated_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
            , PRIMARY KEY(id), CONSTRAINT FK_D385ECA9296CD8AE FOREIGN KEY (team_id) REFERENCES teams (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_D385ECA9A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D385ECA9296CD8AE ON team_users (team_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D385ECA9A76ED395 ON team_users (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_D385ECA9296CD8AEA76ED395 ON team_users (team_id, user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE teams (id CHAR(36) NOT NULL --(DC2Type:uuid)
            , name VARCHAR(255) NOT NULL, external_id VARCHAR(255) NOT NULL, active BOOLEAN NOT NULL, metadata CLOB NOT NULL --(DC2Type:json)
            , deleted_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
            , created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
            , updated_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
            , PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_96C222589F75D7B0 ON teams (external_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE users (id CHAR(36) NOT NULL --(DC2Type:uuid)
            , email_value VARCHAR(255) DEFAULT NULL, phone_number_value VARCHAR(50) DEFAULT NULL, active BOOLEAN NOT NULL, preferences CLOB NOT NULL --(DC2Type:json)
            , created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
            , updated_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
            , name_first_name VARCHAR(255) NOT NULL, name_last_name VARCHAR(255) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_1483A5E9803A19BB ON users (email_value)
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
        $this->addSql(<<<'SQL'
            DROP TABLE notification_preferences
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE notifications
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE payment
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE penalties
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE penalty_types
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE reports
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE team_users
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE teams
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE users
        SQL);
    }
}
