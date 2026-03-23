<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260210190000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create habits table (HabitEntity)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE habits (
            id SERIAL PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT DEFAULT NULL,
            category_id INT DEFAULT NULL REFERENCES categories(id) ON DELETE SET NULL,
            user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
            frequency VARCHAR(32) NOT NULL,
            preferred_time TIME(0) WITHOUT TIME ZONE DEFAULT NULL,
            progress INT NOT NULL DEFAULT 0,
            goal_id INT DEFAULT NULL REFERENCES goals(id) ON DELETE SET NULL,
            status VARCHAR(32) NOT NULL DEFAULT \'active\',
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
            deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL
        )');
        $this->addSql('CREATE INDEX idx_habits_category ON habits(category_id)');
        $this->addSql('CREATE INDEX idx_habits_user ON habits(user_id)');
        $this->addSql('CREATE INDEX idx_habits_goal ON habits(goal_id)');
        $this->addSql('CREATE INDEX idx_habits_deleted_at ON habits(deleted_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE habits');
    }
}
