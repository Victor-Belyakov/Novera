<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260210240000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add optional telegram_id to users';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users ADD telegram_id VARCHAR(32) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users DROP COLUMN telegram_id');
    }
}
