<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260210210000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add status (completed|skipped) to habit_logs';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE habit_logs ADD status VARCHAR(16) NOT NULL DEFAULT 'completed'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE habit_logs DROP status');
    }
}
