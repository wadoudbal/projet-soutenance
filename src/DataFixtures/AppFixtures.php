<?php

namespace App\DataFixtures;

use App\Entity\Exercise;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;


class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
{
    $ex1 = new Exercise();
    $ex1->setName('Développé couché');
    $ex1->setMuscleGroup('Pectoraux');
    $manager->persist($ex1);

    $ex2 = new Exercise();
    $ex2->setName('Squat');
    $ex2->setMuscleGroup('Jambes');
    $manager->persist($ex2);

    $manager->flush();
}
}
