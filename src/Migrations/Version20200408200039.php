<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200408200039 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Generate user API keys and default Screen service times';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('update `app_user` u set api_key=SUBSTRING(PASSWORD(u.email) from 2)');

        $this->addSql('UPDATE `screen` SET std_from=1, std_to=7,sth_from=6,sth_to=23');
    }

    public function down(Schema $schema) : void
    {
    }
}
