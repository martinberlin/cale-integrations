<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201018085228 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add new API 9: Etherscan.io get the balance and last transactions in the ETH Blockchain';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql("INSERT INTO `app_api_category` (`id`, `parent_id`, `name`, `hidden`) VALUES ('21', NULL, 'Cryptocurrency', '0')");
        $this->addSql("INSERT INTO `app_api` (`id`, `api_cat_id`, `url_name`, `name`, `auth_note`, `url`, `request_parameters`, `response_type`, 
`is_location_api`, `documentation_url`, `default_json_settings`, `json_route`, `edit_route`) VALUES 
('9', '21', 'etherscan', 'Etherscan.io', 'API Key', 'https://api.etherscan.io/api?module=[module]&action=[action]&address=[address]&apikey=[apikey]',
 '{\"module\":\"url\",\"action\":\"url\",\"address\":\"url\",\"apikey\":\"url\",\"tag\":\"GET\"}',
  'json', '0', 'https://etherscan.io/apis', '{\"tag\":\"latest\"}', 'App\\\Controller\\\Render\\\ERenderController::render_etherscan', 'api_etherscan')
        ");
    }

    public function down(Schema $schema) : void
    {
        $this->addSql("DELETE FROM app_api_category where id=21");
        $this->addSql("DELETE FROM app_api where id=9");
    }
}
