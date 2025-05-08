<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250323105409 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE app_apilog ADD client_ip VARCHAR(15) DEFAULT NULL, ADD times_read INT NOT NULL');
        $this->addSql('ALTER TABLE app_user_api_log ADD show_xtick_chart1 TINYINT(1) DEFAULT \'1\' NOT NULL, ADD show_xtick_chart2 TINYINT(1) DEFAULT \'1\' NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE app_apilog DROP client_ip, DROP times_read');
        $this->addSql('ALTER TABLE app_user_api_log DROP show_xtick_chart1, DROP show_xtick_chart2');
    }
}
