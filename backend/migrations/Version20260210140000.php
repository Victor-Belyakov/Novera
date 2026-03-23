<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260210140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create tasks table (PostgreSQL)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE tasks (
            id SERIAL PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT DEFAULT NULL,
            assignee_id INT DEFAULT NULL REFERENCES users(id) ON DELETE SET NULL,
            created_by_id INT DEFAULT NULL REFERENCES users(id) ON DELETE SET NULL,
            parent_id INT DEFAULT NULL REFERENCES tasks(id) ON DELETE SET NULL,
            target INT DEFAULT NULL,
            status VARCHAR(50) NOT NULL,
            due_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            priority VARCHAR(50) NOT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
            deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL
        )');
        $this->addSql('CREATE INDEX idx_tasks_assignee ON tasks(assignee_id)');
        $this->addSql('CREATE INDEX idx_tasks_created_by ON tasks(created_by_id)');
        $this->addSql('CREATE INDEX idx_tasks_parent ON tasks(parent_id)');
        $this->addSql('CREATE INDEX idx_tasks_status ON tasks(status)');
        $this->addSql('CREATE INDEX idx_tasks_due_date ON tasks(due_date)');
        $this->addSql('CREATE INDEX idx_tasks_deleted_at ON tasks(deleted_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE tasks');
    }
}
