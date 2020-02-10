<?php

namespace App\DataFixtures;

use App\Entity\Api;
use App\Entity\ApiCategory;
use App\Entity\Display;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ApiCategoryFixtures extends Fixture implements FixtureGroupInterface
{

    public function load(ObjectManager $manager)
    {
        $cat = new ApiCategory();
        $cat->setId(1);
        $cat->setName('Calendar');
        $cat->setHidden(false);
        $manager->persist($cat);

        $cat = new ApiCategory();
        $cat->setId(2);
        $cat->setName('Weather');
        $cat->setHidden(false);
        $manager->persist($cat);

        $cat = new ApiCategory();
        $cat->setId(3);
        $cat->setName('Music');
        $manager->persist($cat);

        $cat = new ApiCategory();
        $cat->setId(4);
        $cat->setName('News');
        $manager->persist($cat);

        $cat = new ApiCategory();
        $cat->setId(5);
        $cat->setName('Education');
        $manager->persist($cat);

        $cat = new ApiCategory();
        $cat->setId(6);
        $cat->setName('Development & IT');
        $manager->persist($cat);

        $cat = new ApiCategory();
        $cat->setId(7);
        $cat->setName('Utilities');
        $manager->persist($cat);

        $cat = new ApiCategory();
        $cat->setId(8);
        $cat->setName('Entertainment');
        $manager->persist($cat);

        $cat = new ApiCategory();
        $cat->setId(9);
        $cat->setName('Photo & Video');
        $manager->persist($cat);

        $cat = new ApiCategory();
        $cat->setId(10);
        $cat->setName('Food & Drinks');
        $manager->persist($cat);

        $cat = new ApiCategory();
        $cat->setId(11);
        $cat->setName('Health & Fitness');
        $manager->persist($cat);

        $cat = new ApiCategory();
        $cat->setId(12);
        $cat->setName('Productivity');
        $manager->persist($cat);

        $cat = new ApiCategory();
        $cat->setId(13);
        $cat->setName('Finance');
        $manager->persist($cat);

        $cat = new ApiCategory();
        $cat->setId(14);
        $cat->setName('Games');
        $manager->persist($cat);

        $cat = new ApiCategory();
        $cat->setId(15);
        $cat->setName('Business');
        $manager->persist($cat);

        $cat = new ApiCategory();
        $cat->setId(16);
        $cat->setName('Social networking');
        $manager->persist($cat);

        $cat = new ApiCategory();
        $cat->setId(17);
        $cat->setName('Lifestyle');
        $manager->persist($cat);

        $cat = new ApiCategory();
        $cat->setId(18);
        $cat->setName('Sports');
        $manager->persist($cat);

        $cat = new ApiCategory();
        $cat->setId(19);
        $cat->setName('Travel');
        $manager->persist($cat);

        $cat = new ApiCategory();
        $cat->setId(20);
        $cat->setName('Medical');
        $manager->persist($cat);

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
