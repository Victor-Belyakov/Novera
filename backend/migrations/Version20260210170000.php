<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260210170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add goals.completed (nullable boolean): null = just set, true = done, false = not done';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE goals ADD completed BOOLEAN DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE goals DROP COLUMN completed');
    }
}
