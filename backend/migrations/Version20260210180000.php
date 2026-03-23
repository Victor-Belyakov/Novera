<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260210180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'GoalEntity: add description, reason, type, status, progress, priority, archived; created_by_id NOT NULL + CASCADE';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE goals ADD description TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE goals ADD reason TEXT DEFAULT NULL');
        $this->addSql("ALTER TABLE goals ADD type VARCHAR(32) NOT NULL DEFAULT 'result'");
        $this->addSql("ALTER TABLE goals ADD status VARCHAR(32) NOT NULL DEFAULT 'active'");
        $this->addSql('ALTER TABLE goals ADD progress INT NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE goals ADD priority SMALLINT NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE goals ADD archived BOOLEAN NOT NULL DEFAULT false');

        $this->addSql('ALTER TABLE goals DROP CONSTRAINT IF EXISTS goals_created_by_id_fkey');
        $this->addSql('UPDATE goals SET created_by_id = (SELECT id FROM users LIMIT 1) WHERE created_by_id IS NULL');
        $this->addSql('ALTER TABLE goals ALTER COLUMN created_by_id SET NOT NULL');
        $this->addSql('ALTER TABLE goals ADD CONSTRAINT goals_created_by_id_fkey FOREIGN KEY (created_by_id) REFERENCES users(id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE goals DROP CONSTRAINT IF EXISTS goals_created_by_id_fkey');
        $this->addSql('ALTER TABLE goals ALTER COLUMN created_by_id DROP NOT NULL');
        $this->addSql('ALTER TABLE goals ADD CONSTRAINT goals_created_by_id_fkey FOREIGN KEY (created_by_id) REFERENCES users(id) ON DELETE SET NULL');

        $this->addSql('ALTER TABLE goals DROP COLUMN archived');
        $this->addSql('ALTER TABLE goals DROP COLUMN priority');
        $this->addSql('ALTER TABLE goals DROP COLUMN progress');
        $this->addSql('ALTER TABLE goals DROP COLUMN status');
        $this->addSql('ALTER TABLE goals DROP COLUMN type');
        $this->addSql('ALTER TABLE goals DROP COLUMN reason');
        $this->addSql('ALTER TABLE goals DROP COLUMN description');
    }
}
