<?php

namespace App\DataFixtures;

use App\Entity\Ad;
use Faker\Factory;
use App\Entity\User;
use App\Entity\Image;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('FR-fr');

        // Gestion des utilisateurs
        $users = [];
        $genres = ['male','female'];

        for($i = 0 ; $i < 10 ; $i++)
        {
            $user = new User();

            $genre = $faker->randomElement($genres);
            $picture = 'https://randomuser.me/api/portraits/';
            $picture .= ($genre == 'male' ? 'men/' : 'women/') . mt_rand(1,99) . '.jpg';

            $user->setFirstName($faker->firstname($genre))
                 ->setLastName($faker->lastname)
                 ->setEmail($faker->email)
                 ->setIntroduction($faker->sentence())
                 ->setDescription('<p>' . join('</p><p>',$faker->paragraphs(5)) . '</p>')
                 ->setHash($this->encoder->encodePassword($user,'password'))
                 ->setPicture($picture);
            
            $manager->persist($user);
            $users[] = $user;
        }

        // Gestion des annonces
        for($i = 0 ; $i < 20 ; $i++)
        {
            $ad = new Ad();

            $user = $users[mt_rand(0,count($users)-1)];

            $ad->setTitle($faker->sentence())
               ->setCoverImage($faker->imageUrl(1000,350))
               ->setIntroduction($faker->paragraph(2))
               ->setContent('<p>' . join('</p><p>',$faker->paragraphs(5)) . '</p>')
               ->setPrice(mt_rand(40,200))
               ->setRooms(mt_rand(1,5))
               ->setAuthor($user);
            
            for($j = 1 ; $j <= mt_rand(2,5) ; $j++)
            {
                $image = new Image();

                $image->setUrl($faker->imageUrl())
                      ->setCaption($faker->sentence())
                      ->setAd($ad);

                $manager->persist($image);
            }

            $manager->persist($ad);
        }

        $manager->flush();
    }
}
