<?php

namespace App\DataFixtures;

use App\Entity\Api;
use App\Entity\Display;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class DisplayFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $AMAZON_REF = 'singandkaraok';
        $api = new Display();
        $api->setId(1);
        $api->setClassName('GDEH0213B73');
        $api->setName('GDEH0213B73 or B72 2.13" b/w');
        $api->setBrand('Good display');
        $api->setWidth(250);
        $api->setHeight(122);
        $api->setGrayLevels(2);
        $api->setActiveSize('23.7*48.55');
        $api->setTimeOfRefresh(4);
        $api->setManualUrl('http://www.e-paper-display.com/products_detail/productId=458.html');
        $api->setPurchaseUrl("https://www.amazon.com/dp/B07KZRJX4D?ref=$AMAZON_REF");
        $manager->persist($api);

        $api = new Display();
        $api->setId(2);
        $api->setClassName('GDEH029A1');
        $api->setName('GDEH029A1 2.9" b/w');
        $api->setBrand('Good display');
        $api->setWidth(296);
        $api->setHeight(128);
        $api->setGrayLevels(1);
        $api->setActiveSize('66.9*29.1');
        $api->setTimeOfRefresh(3);
        $api->setManualUrl('http://www.e-paper-display.com/products_detail/productId=251.html');
        $api->setPurchaseUrl("https://www.amazon.com/dp/B078HFM4GQ?ref=$AMAZON_REF");
        $manager->persist($api);

        $api = new Display();
        $api->setId(3);
        $api->setClassName('GDEW029T5');
        $api->setName('GDEW029T5 2.9" b/w');
        $api->setBrand('Good display');
        $api->setWidth(296);
        $api->setHeight(128);
        $api->setGrayLevels(4);
        $api->setActiveSize('66.9*29.06');
        $api->setTimeOfRefresh(3);
        $api->setManualUrl('http://www.e-paper-display.com/products_detail/productId=347.html');
        $api->setPurchaseUrl("https://www.amazon.com/dp/B078H5HX3Z?ref=$AMAZON_REF");
        $manager->persist($api);

        $api = new Display();
        $api->setId(4);
        $api->setClassName('GDEW0371W7');
        $api->setName('GDEW0371W7 3.7" b/w');
        $api->setBrand('Good display');
        $api->setWidth(416);
        $api->setHeight(240);
        $api->setGrayLevels(4);
        $api->setActiveSize('81.5*47');
        $api->setTimeOfRefresh(4);
        $api->setManualUrl('http://www.e-paper-display.com/products_detail/productId=401.html');
        $api->setPurchaseUrl("https://www.amazon.com/dp/B07MC3HMZ8?ref=$AMAZON_REF");
        $manager->persist($api);

        $api = new Display();
        $api->setId(5);
        $api->setClassName('GDEW042T2');
        $api->setName('GDEW042T2 4.2" b/w');
        $api->setBrand('Good display');
        $api->setWidth(400);
        $api->setHeight(300);
        $api->setGrayLevels(4);
        $api->setActiveSize('84.8*63.6');
        $api->setTimeOfRefresh(4);
        $api->setManualUrl('http://www.e-paper-display.com/products_detail/productId=321.html');
        $api->setPurchaseUrl("https://www.amazon.com/dp/B078H4DBR1?ref=$AMAZON_REF");
        $manager->persist($api);

        $api = new Display();
        $api->setId(6);
        $api->setClassName('GDEW0583T7');
        $api->setName('GDEW0583T7 5.83" b/w');
        $api->setBrand('Good display');
        $api->setWidth(600);
        $api->setHeight(448);
        $api->setGrayLevels(4);
        $api->setActiveSize('118.8*88.2');
        $api->setTimeOfRefresh(3);
        $api->setManualUrl('http://www.e-paper-display.com/products_detail/productId=387.html');
        $api->setPurchaseUrl("https://www.amazon.com/dp/B07BRM9RLQ?ref=$AMAZON_REF");
        $manager->persist($api);

        $api = new Display();
        $api->setId(7);
        $api->setClassName('GDEW075T8');
        $api->setName('GDEW075T8 7.5" b/w');
        $api->setBrand('Good display/Waveshare');
        $api->setWidth(640);
        $api->setHeight(384);
        $api->setGrayLevels(1);
        $api->setActiveSize('163.2*97.92');
        $api->setTimeOfRefresh(4);
        $api->setManualUrl('http://www.e-paper-display.com/products_detail/productId=323.html');
        $api->setPurchaseUrl("https://www.amazon.com/dp/B078JJCDSX?ref=$AMAZON_REF");
        $manager->persist($api);

        $api = new Display();
        $api->setId(8);
        $api->setClassName('GDEW075T7');
        $api->setName('GDEW075T7 7.5" b/w');
        $api->setBrand('Good display/Waveshare');
        $api->setWidth(800);
        $api->setHeight(480);
        $api->setGrayLevels(4);
        $api->setActiveSize('163.2*97.92');
        $api->setTimeOfRefresh(4);
        $api->setManualUrl('http://www.e-paper-display.com/products_detail/productId=456.html');
        $api->setPurchaseUrl("https://www.amazon.com/dp/B07T5JWG5Y?ref=$AMAZON_REF");
        $manager->persist($api);


        $api = new Display();
        $api->setId(9);
        $api->setClassName('GDEW027W3');
        $api->setName('GDEW027W3 2.7" b/w 4 buttons');
        $api->setBrand('Waveshare');
        $api->setWidth(264);
        $api->setHeight(176);
        $api->setGrayLevels(4);
        $api->setActiveSize('57.2*38.2');
        $api->setTimeOfRefresh(6);
        $api->setManualUrl('https://www.waveshare.com/product/displays/e-paper/epaper-2/2.7inch-e-paper-hat.htm');
        $api->setPurchaseUrl("https://www.amazon.com/Resolution-Interface-Embedded-Controller-Raspberry/dp/B075FWLMRV/ref=$AMAZON_REF");
        $manager->persist($api);

        $manager->flush();
    }
}
