<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200427214557 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add API 7: OpenWeather';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql("INSERT INTO `app_api` (`id`, `url_name`, `name`, `url`,
 `request_parameters`, `response_type`, `is_location_api`, 
 `documentation_url`, `default_json_settings`, `api_cat_id`, `auth_note`,
 `json_route`, `edit_route`) 
VALUES ('7', 'openweather', 'OpenWeather', 'https://api.openweathermap.org/data/2.5/forecast?lat=[latitude]&lon=[longitude]&APPID=[token]', 
'{\"token\":\"url\",\"latitude\":\"url\",\"longitude\":\"url\",\"units\":\"GET\",\"lang\":\"GET\"}', 'json', '1', 
'https://openweathermap.org/api/hourly-forecast', NULL, '2', 'API Key', 
'App\Controller\Render\ARenderController::render_openweather', 'b_api_customize_location')");
    }

    public function down(Schema $schema) : void
    {
        $this->addSql("DELETE FROM app_api WHERE id=7");
    }
}
