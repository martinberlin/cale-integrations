<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250321062425 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE app_user_api_log ADD color2 VARCHAR(7) DEFAULT NULL, ADD color3 VARCHAR(7) DEFAULT NULL, ADD exclude1 TINYINT(1) DEFAULT \'1\' NOT NULL, ADD exclude2 TINYINT(1) DEFAULT \'0\' NOT NULL, ADD additional_chart_co2 TINYINT(1) DEFAULT \'0\' NOT NULL, ADD co2_chart_type VARCHAR(20) DEFAULT NULL, CHANGE axis_font_file axis_font_file VARCHAR(50) DEFAULT NULL, CHANGE axis_font_size axis_font_size INT DEFAULT NULL, CHANGE color color1 VARCHAR(7) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE app_user_api_log ADD color VARCHAR(7) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, DROP color1, DROP color2, DROP color3, DROP exclude1, DROP exclude2, DROP additional_chart_co2, DROP co2_chart_type, CHANGE axis_font_file axis_font_file VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE axis_font_size axis_font_size INT NOT NULL');
    }
}
