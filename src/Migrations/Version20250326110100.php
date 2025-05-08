<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250326110100 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE app_user_api_log ADD telemetry_cargo VARCHAR(50) DEFAULT NULL, ADD telemetry_device VARCHAR(50) DEFAULT NULL, ADD telemetry_api_key VARCHAR(50) DEFAULT NULL, ADD telemetry_ingest_url VARCHAR(150) DEFAULT NULL, ADD telemetry_cargo2 VARCHAR(50) DEFAULT NULL, ADD telemetry_device2 VARCHAR(50) DEFAULT NULL, ADD telemetry_api_key2 VARCHAR(50) DEFAULT NULL, ADD telemetry_ingest_url2 VARCHAR(150) DEFAULT NULL, ADD telemetry_active TINYINT(1) DEFAULT \'0\' NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE app_user_api_log DROP telemetry_cargo, DROP telemetry_device, DROP telemetry_api_key, DROP telemetry_ingest_url, DROP telemetry_cargo2, DROP telemetry_device2, DROP telemetry_api_key2, DROP telemetry_ingest_url2, DROP telemetry_active');
    }
}
