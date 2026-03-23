<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260210160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add categories and goals tables; replace tasks.target with tasks.goal_id';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE categories (
            id SERIAL PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
            deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL
        )');
        $this->addSql('CREATE INDEX idx_categories_deleted_at ON categories(deleted_at)');

        $this->addSql('CREATE TABLE goals (
            id SERIAL PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            category_id INT DEFAULT NULL REFERENCES categories(id) ON DELETE SET NULL,
            due_date DATE DEFAULT NULL,
            created_by_id INT DEFAULT NULL REFERENCES users(id) ON DELETE SET NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
            deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL
        )');
        $this->addSql('CREATE INDEX idx_goals_category ON goals(category_id)');
        $this->addSql('CREATE INDEX idx_goals_created_by ON goals(created_by_id)');
        $this->addSql('CREATE INDEX idx_goals_deleted_at ON goals(deleted_at)');

        $this->addSql('ALTER TABLE tasks ADD goal_id INT DEFAULT NULL REFERENCES goals(id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX idx_tasks_goal ON tasks(goal_id)');
        $this->addSql('ALTER TABLE tasks DROP COLUMN target');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tasks ADD target INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tasks DROP COLUMN goal_id');
        $this->addSql('DROP TABLE goals');
        $this->addSql('DROP TABLE categories');
    }
}
