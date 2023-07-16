## CALE Integrations

**CALE is a dynamic way to organize and fetch content from different sources including weather, todo lists and any applications that expose an open API.**
The goal is to have a screen administrator where you can mix content of different APIs and control the content output. 

The [ESP32 firmware](https://github.com/martinberlin/eink-calendar/tree/cale) is easy to install, free and open source
If you are interested in building one E-Ink calendar you can find information to build one in the
[CALE Hackaday project](https://hackaday.io/project/169086)

### [YouTube video explaining how Cale concept works](https://www.youtube.com/watch?v=7Sal9Ii7H2U)

## API Pool includes

    Codename        | API
    cale-google     | Google calendar
    cale-timetree   | Shared calendar
    weather-darksky | Darksky weather -> deprecated (Bought by Apply, public API dead)
    cale-iCal       | CALE iCal API
    cale-html       | CALE internal HTML editor
    
Check the updated list of [supported APIs in the public website](https://cale.es/apis)

## Technologies used

- PHP / Symfony 4.4
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
    
### PHP Minimum version: 7.3

Requires PHP extensions:

- ext-simplexml
- ext-gd (Used by endroid/qr-code)
- ext-mbstring
    
### Thanks to:

- https://wkhtmltopdf.org
- https://twitter.com/IoTPanic Samuel that helped with my basic know-how of C
- https://summernote.org out HTML editor of choice (Sponsored their project)
- All the first users that trusted in the project and send bug reports, emails, along with wishes and support.
- [UsefulElectronics](https://github.com/UsefulElectronics) who collaborated correcting openWeather API and also in the [Cale-idf epaper component repository](https://github.com/martinberlin/cale-idf/graphs/contributors)

Nothing would be possible without that feedback and help!
