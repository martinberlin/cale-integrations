<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210123171453 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'New API: Crypto prices';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("INSERT INTO `app_api` (`id`, `url_name`, `name`, `url`, `request_parameters`, `response_type`, `is_location_api`, `documentation_url`, `default_json_settings`, `api_cat_id`, `auth_note`, `json_route`, edit_route) 
        VALUES 
        ('10', 'cale-crypto-financial', 'CALE Crypto candlestick charts', 'https://cale.es', NULL, 'html', NULL, NULL, NULL, 21, 'Internal: No Auth needed', 'App\\\Controller\\\Render\\\FRenderController::render_int_crypto', 'b_api_wizard_cale-crypto')"
        );

        $this->addSql("CREATE TABLE app_user_api_financial (user_id INT NOT NULL, user_api_id VARCHAR(40) NOT NULL, width INT NOT NULL, height INT NOT NULL, data_rows INT NOT NULL, candle_type VARCHAR(20) DEFAULT NULL, symbol VARCHAR(20) DEFAULT NULL, timeseries VARCHAR(20) DEFAULT NULL, color_ascending VARCHAR(7) DEFAULT NULL, color_descending VARCHAR(7) DEFAULT NULL, INDEX IDX_9DCB69FFA76ED395 (user_id), INDEX IDX_9DCB69FF31C42205 (user_api_id), UNIQUE INDEX user_api_name_idx (user_id, user_api_id), PRIMARY KEY(user_id, user_api_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;");
        $this->addSql("ALTER TABLE app_user_api_financial ADD CONSTRAINT FK_9DCB69FFA76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id);");
        $this->addSql("ALTER TABLE app_user_api_financial ADD CONSTRAINT FK_9DCB69FF31C42205 FOREIGN KEY (user_api_id) REFERENCES app_int_api (uuid);");
    }

    public function down(Schema $schema) : void
    {
        $this->addSql("DELETE FROM app_api WHERE id=10");
    }
}
