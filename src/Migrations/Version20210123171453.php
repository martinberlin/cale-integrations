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
    }

    public function down(Schema $schema) : void
    {
        $this->addSql("DELETE FROM app_api WHERE id=10");
    }
}
