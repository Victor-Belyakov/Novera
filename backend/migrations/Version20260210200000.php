<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260210200000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add habit_logs table and target column to habits';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE habits ADD target INT DEFAULT NULL');
        $this->addSql('CREATE TABLE habit_logs (
            id SERIAL PRIMARY KEY,
            habit_id INT NOT NULL REFERENCES habits(id) ON DELETE CASCADE,
            logged_at DATE NOT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
            deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            UNIQUE(habit_id, logged_at)
        )');
        $this->addSql('CREATE INDEX idx_habit_logs_habit_id ON habit_logs(habit_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE habit_logs');
        $this->addSql('ALTER TABLE habits DROP target');
    }
}
