<?php

namespace App\DataFixtures;

use App\Entity\Api;
use App\Entity\ApiCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AppFixtures extends Fixture  implements FixtureGroupInterface,ContainerAwareInterface
{
    private $doctrineManager;
    private $categoryRepository;
    /**
     * @param ContainerInterface|null $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->doctrineManager = $container->get('doctrine')->getManager();
        $this->categoryRepository = $this->doctrineManager->getRepository(ApiCategory::class);
    }
    public function load(ObjectManager $manager)
    {
        $api = new Api();
        $api->setId(1);
        $api->setCategory($this->categoryRepository->findOneBy(['name'=>'Calendar']));
        $api->setUrlName('cale-google');
        $api->setDocumentationUrl('https://developers.google.com/calendar/quickstart/php');
        $api->setName('Google Calendar');
        $api->setResponseType('json');
        $api->setAuthNote('Oauth Authorization code');
        $api->setIsLocationApi(false);
        $defJsonArr = [
            'orderBy' => 'startTime',
            'singleEvents' => true];
        $api->setDefaultJsonSettings(json_encode($defJsonArr));
        $manager->persist($api);

        $requestParams = [
            'token'     => 'url',
            'timezone'  => 'GET',
            'days'      => 'GET'
        ];
        $api = new Api();
        $api->setId(2);
        $api->setCategory($this->categoryRepository->findOneBy(['name'=>'Calendar']));
        $api->setUrlName('cale-timetree');
        $api->setName('Timetree shared Calendar');
        $api->setUrl('https://timetreeapis.com/calendars');
        $api->setDocumentationUrl('https://developers.timetreeapp.com/en/docs/api');
        $api->setRequestParameters(json_encode($requestParams));
        $api->setResponseType('json');
        $api->setAuthNote('Personal access token');
        $api->setIsLocationApi(false);

        $defJsonArr = [
            'days' => 7,
            'include' => 'creator,label,attendees'];
        $api->setDefaultJsonSettings(json_encode($defJsonArr));
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
        $api->setCategory($this->categoryRepository->findOneBy(['name'=>'Weather']));
        $api->setUrlName('weather-darksky');
        $api->setName('Darksky weather forecasts');
        $api->setUrl('https://api.darksky.net/forecast/[token]/[latitude],[longitude]');
        // Additional advanced settings that are sent in the URL
        $api->setDefaultJsonSettings('{"exclude":"currently,minutely,alerts,flags","units":"si"}');
        $api->setDocumentationUrl('https://darksky.net/dev/docs');
        $api->setRequestParameters(json_encode($requestParams));
        $api->setResponseType('json');
        $api->setAuthNote('Authorization key');
        $api->setIsLocationApi(true);
        $api->setJsonRoute('json_weather_generic');
        $manager->persist($api);
        $manager->flush();
    }


    /**
     * @return array
     * Will execute only this Fixtures when: bin/console doctrine:fixtures:load --group=cat
     */
    public static function getGroups(): array
    {
        return ['apis'];
    }
}
