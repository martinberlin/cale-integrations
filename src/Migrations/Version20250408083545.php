<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250408083545 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE app_apilog_ampere (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, api VARCHAR(40) DEFAULT NULL, fp DOUBLE PRECISION NOT NULL, total_wh DOUBLE PRECISION NOT NULL, volt INT DEFAULT NULL, watt INT DEFAULT NULL, hour INT DEFAULT NULL, datestamp DATETIME NOT NULL, timestamp INT NOT NULL, times_read INT NOT NULL, INDEX IDX_A9F5833CA76ED395 (user_id), INDEX IDX_A9F5833CAD05D80F (api), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_apilog_ampere_daily (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, api VARCHAR(40) DEFAULT NULL, total_wh DOUBLE PRECISION NOT NULL, datestamp DATETIME NOT NULL, hour INT NOT NULL, timezone VARCHAR(100) DEFAULT NULL, INDEX IDX_E64ACA18A76ED395 (user_id), INDEX IDX_E64ACA18AD05D80F (api), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_user_ampere_settings (user_id INT NOT NULL, user_api_id VARCHAR(40) NOT NULL, reset_counter_day INT NOT NULL, cost_kilowatt_hour DOUBLE PRECISION DEFAULT NULL, width INT NOT NULL, height INT NOT NULL, data_rows INT NOT NULL, candle_type VARCHAR(20) DEFAULT NULL, candle_type2 VARCHAR(20) DEFAULT NULL, timezone VARCHAR(200) DEFAULT NULL, datetime_last_reset DATETIME DEFAULT NULL, color1 VARCHAR(7) DEFAULT NULL, color2 VARCHAR(7) DEFAULT NULL, exclude1 TINYINT(1) DEFAULT \'1\' NOT NULL, axis_font_file VARCHAR(50) DEFAULT NULL, axis_font_size INT DEFAULT NULL, INDEX IDX_58B43457A76ED395 (user_id), INDEX IDX_58B4345731C42205 (user_api_id), UNIQUE INDEX user_api_name_idx (user_id, user_api_id), PRIMARY KEY(user_id, user_api_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE app_apilog_ampere ADD CONSTRAINT FK_A9F5833CA76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id)');
        $this->addSql('ALTER TABLE app_apilog_ampere ADD CONSTRAINT FK_A9F5833CAD05D80F FOREIGN KEY (api) REFERENCES app_int_api (uuid)');
        $this->addSql('ALTER TABLE app_apilog_ampere_daily ADD CONSTRAINT FK_E64ACA18A76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id)');
        $this->addSql('ALTER TABLE app_apilog_ampere_daily ADD CONSTRAINT FK_E64ACA18AD05D80F FOREIGN KEY (api) REFERENCES app_int_api (uuid)');
        $this->addSql('ALTER TABLE app_user_ampere_settings ADD CONSTRAINT FK_58B43457A76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id)');
        $this->addSql('ALTER TABLE app_user_ampere_settings ADD CONSTRAINT FK_58B4345731C42205 FOREIGN KEY (user_api_id) REFERENCES app_int_api (uuid)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE app_apilog_ampere');
        $this->addSql('DROP TABLE app_apilog_ampere_daily');
        $this->addSql('DROP TABLE app_user_ampere_settings');
    }
}
