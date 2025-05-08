<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250317141042 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add ApiLog table';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE app_apilog (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, api VARCHAR(40) DEFAULT NULL,
temperature FLOAT NOT NULL, humidity FLOAT NOT NULL, co2 INT NOT NULL, light NUMERIC(2, 0) DEFAULT NULL, 
timezone VARCHAR(100) DEFAULT NULL , timestamp INT NULL, datestamp DATETIME DEFAULT NULL,
INDEX IDX_58EBF94DA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_user_api_log (user_id INT NOT NULL, user_api_id VARCHAR(40) NOT NULL, width INT NOT NULL, height INT NOT NULL, data_rows INT NOT NULL, candle_type VARCHAR(20) DEFAULT NULL, color VARCHAR(7) DEFAULT NULL, color2 VARCHAR(7) DEFAULT NULL, axis_font_file VARCHAR(50) NOT NULL, axis_font_size INT NOT NULL, INDEX IDX_563E2A7AA76ED395 (user_id), INDEX IDX_563E2A7A31C42205 (user_api_id), UNIQUE INDEX user_api_name_idx (user_id, user_api_id), PRIMARY KEY(user_id, user_api_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
ALTER TABLE app_user_api_log ADD CONSTRAINT FK_563E2A7AA76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id);
ALTER TABLE app_user_api_log ADD CONSTRAINT FK_563E2A7A31C42205 FOREIGN KEY (user_api_id) REFERENCES app_int_api (uuid);
ALTER TABLE app_apilog CHANGE temperature temperature NUMERIC(2, 0) NOT NULL, CHANGE humidity humidity NUMERIC(2, 0) NOT NULL, CHANGE timezone timezone VARCHAR(10) DEFAULT NULL, CHANGE timestamp timestamp INT DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE datestamp datestamp DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE app_apilog ADD CONSTRAINT FK_58EBF94DA76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id)');
        $this->addSql('ALTER TABLE app_apilog ADD CONSTRAINT FK_58EBF94DAD05D80F FOREIGN KEY (api) REFERENCES app_int_api (uuid)');
        $this->addSql('CREATE INDEX IDX_58EBF94DAD05D80F ON app_apilog (api)');
        // Insert SCD40 API
        $this->addSql("INSERT INTO `app_api` (`id`, `api_cat_id`, `url_name`, `name`, `auth_note`, `url`, `request_parameters`, `response_type`, `is_location_api`, `documentation_url`, `default_json_settings`, `json_route`, `edit_route`) VALUES
(11, 2, 'https://cale.es', 'SCD40 sensor', 'Internal API Key', '', NULL, 'json', 0, NULL, '{\"co2\":0,\"temperature\":0.1,\"humidity\":0.1,\"client\":{\"id\":USERID,\"key\":\"KEY\",\"timezone\":\"Europe/Madrid\"}}', 'App\\Controller\\Render\\FRenderController::render_scd40', 'b_api_sensor_scd40');
");
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE app_apilog');
        $this->addSql('DROP TABLE app_user_api_log');
        $this->addSql('DELETE FROM app_api WHERE id = 11');
        $this->addSql('ALTER TABLE app_user CHANGE paid_total paid_total INT DEFAULT 0');
    }
}
