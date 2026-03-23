<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260210230000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop reason column from goals';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE goals DROP COLUMN reason');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE goals ADD reason TEXT DEFAULT NULL');
    }
}
