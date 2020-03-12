## CALE Integrations

**CALE is a dynamic way to organize and fetch content from different sources including weather, todo lists and any applications that expose an open API.**
The goal is to have a screen administrator where you can mix content of different APIs and control the content output. 

The [ESP32 firmware](https://github.com/martinberlin/eink-calendar/tree/cale) is easy to install, free and open source
If you are interested in building one E-Ink calendar you can find information to build one in the
[CALE Hackaday project](https://hackaday.io/project/169086)

## API Pool includes

    Codename        | API
    cale-google     | Google calendar
    cale-timetree   | Shared calendar
    weather-darksky | Darksky weather
    cale-iCal       | CALE iCal API
    cale-html       | CALE internal HTML editor
    

## Technologies used

- PHP / Symfony 4.4 recipes 
- Doctrine Object relational mapping
- native Bootstrap CSS / minimal use of jQuery 
- Amazon AWS infrastructure (EC2/EBS/S3)


     A Screenshot microservice based on wk<html>toimage

### Initial install 

    composer install 
    vi .env
    // Configure there your DATABASE_URL
    bin/console doctrine:schema:update --dump-sql
    // Check SQL and do a --force to create tables
    
### Thanks to:

- https://wkhtmltopdf.org
- https://twitter.com/IoTPanic Samuel that helped with my basic know-how of C
- https://summernote.org out HTML editor of choice (Sponsored their project)
- All the first users that trusted in the project and send bug reports, emails, along with wishes and support. 
Nothing would be possible without that feedback 
