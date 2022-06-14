<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        $user = new User();
        $user->setFirstName('');
        $user->setLastName('');
        $user->setBankAccount(1000);
        $manager->persist($user);

        $manager->flush();
    }
}
