<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260210150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add CHECK constraint on tasks.status (TaskStatusEnum values only)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            "ALTER TABLE tasks ADD CONSTRAINT tasks_status_check CHECK (status IN ('new', 'in_progress', 'done', 'closed'))"
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tasks DROP CONSTRAINT tasks_status_check');
    }
}
