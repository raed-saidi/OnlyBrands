<?php

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250602205343 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update product image column to BLOB';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE products MODIFY image LONGBLOB NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE products MODIFY image VARCHAR(255) DEFAULT NULL');
    }
}