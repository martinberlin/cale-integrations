## CALE Integrations

Will be a simple backend built in Symfony 5.
Experimental project with a simple mission: 
To configure a pool of APIs and deliver customized layouts.

## API Pool includes

    Codename | API
    gcale      Google calendar
    darksky    Darksky weather
    timetree   Shared calendar

## Technologies used

PHP / Symfony 5 recipes 
Doctrine Object relational mapping

### Initial install 

    composer install 
    vi .env
    // Configure there your DATABASE_URL
    bin/console doctrine:schema:update --dump-sql
    // Check SQL and do a --force to create tables