<?php

namespace App\DataFixtures;

use App\Entity\Api;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $api = new Api();
        $api->setId(1);
        $api->setUrlName('cale-google');
        $api->setDocumentationUrl('https://developers.google.com/calendar/quickstart/php');
        $api->setName('Google Calendar');
        $api->setResponseType('json');
        $api->setIsLocationApi(false);
        $manager->persist($api);

        $requestParams = [
            'token'     => 'url',
            'timezone'  => 'GET',
            'days'      => 'GET'
        ];
        $api = new Api();
        $api->setId(2);
        $api->setUrlName('cale-timetree');
        $api->setName('Timetree shared Calendar');
        $api->setUrl('https://timetreeapis.com/calendars/[token]/upcoming_events');
        $api->setDocumentationUrl('https://developers.timetreeapp.com/en/docs/api');
        $api->setRequestParameters(json_encode($requestParams));
        $api->setResponseType('json');
        $api->setIsLocationApi(false);
        $manager->persist($api);

        $requestParams = [
            'token'     => 'url',
            'latitude'  => 'url',
            'longitude' => 'url',
            'exclude' => 'GET',
            'units' => 'GET',
            'lang' => 'GET',
        ];
        $api = new Api();
        $api->setId(3);
        $api->setUrlName('weather-darksky');
        $api->setName('Darksky weather forecasts');
        $api->setUrl('https://api.darksky.net/forecast/[token]/[latitude],[longitude]');
        // Additional advanced settings that are sent in the URL
        $api->setDefaultJsonSettings('{"exclude":"currently,minutely,alerts,flags","units":"si"}');
        $api->setDocumentationUrl('https://darksky.net/dev/docs');
        $api->setRequestParameters(json_encode($requestParams));
        $api->setResponseType('json');
        $api->setIsLocationApi(true);
        $api->setIsLocationApi(true);
        $manager->persist($api);
        $manager->flush();
    }
}
