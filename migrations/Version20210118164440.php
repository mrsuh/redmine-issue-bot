<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210118164440 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TABLE status (redmine_id INTEGER NOT NULL, redmine_name VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, for_review BOOLEAN NOT NULL, PRIMARY KEY(redmine_id))');
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT login FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TABLE user (redmine_id INTEGER NOT NULL, redmine_login VARCHAR(255) NOT NULL, telegram_login VARCHAR(255) NOT NULL, PRIMARY KEY(redmine_id))');
        $this->addSql('INSERT INTO user (redmine_login) SELECT login FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP TABLE status');
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT  FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, login VARCHAR(255) NOT NULL COLLATE BINARY, current_task_id INTEGER DEFAULT NULL, current_task_started_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , track_time BOOLEAN NOT NULL, active BOOLEAN NOT NULL)');
        $this->addSql('INSERT INTO user () SELECT  FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
    }
}
