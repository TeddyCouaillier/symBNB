<?php

namespace App\DataFixtures;

use App\Entity\Ad;
use Faker\Factory;
use App\Entity\Role;
use App\Entity\User;
use App\Entity\Image;
use App\Entity\Booking;
use App\Entity\Comment;
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

        // Gestion des roles
        $adminRole = new Role();
        $adminRole->setTitle('ROLE_ADMIN');
        $manager->persist($adminRole);

        $adminUser = new User();
        $adminUser->setFirstName('Teddy')
                  ->setLastName('Couaillier')
                  ->setEmail('ted@gmail.com')
                  ->setHash($this->encoder->encodePassword($adminUser, 'password'))
                  ->setPicture('https://scontent-cdg2-1.xx.fbcdn.net/v/t1.0-9/27654408_10210669030751827_8858703346832072447_n.jpg?_nc_cat=108&_nc_ht=scontent-cdg2-1.xx&oh=7019a0551f7787c0ce507bfe4ca77612&oe=5D9333F0')
                  ->setIntroduction($faker->sentence())
                  ->setDescription('<p>' . join('</p><p>',$faker->paragraphs(5)) . '</p>')
                  ->addUserRole($adminRole);

        $manager->persist($adminUser);

        // Gestion des utilisateurs
        $users = [];
        $genres = ['male','female'];

        for($i = 0 ; $i < 10 ; $i++)
        {
            $user = new User();

            $genre    = $faker->randomElement($genres);
            $picture  = 'https://randomuser.me/api/portraits/';
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
               ->setCoverImage('http://lorempixel.com/1000/350/?'.mt_rand(1,100000))
               ->setIntroduction($faker->paragraph(2))
               ->setContent('<p>' . join('</p><p>',$faker->paragraphs(5)) . '</p>')
               ->setPrice(mt_rand(40,200))
               ->setRooms(mt_rand(1,5))
               ->setAuthor($user);
            
            for($j = 1 ; $j <= mt_rand(2,5) ; $j++)
            {
                $image = new Image();

                $image->setUrl('http://lorempixel.com/600/650/?'.mt_rand(1,100000))
                      ->setCaption($faker->sentence())
                      ->setAd($ad);

                $manager->persist($image);
            }

            // Gestion des réservations
            for($j = 0 ; $j < mt_rand(0,10) ; $j++)
            {
                $booking = new Booking();
                
                $createdAt = $faker->dateTimeBetween('-6 months');
                $startDate = $faker->dateTimeBetween('-3 months');
                $duration  = mt_rand(3,10);
                $endDate   = (clone $startDate)->modify("+$duration days");
                $amount    = $ad->getPrice() * $duration;
                $booker    = $users[mt_rand(0, count($users) -1)];
                $comment   = $faker->paragraph();

                $booking->setCreatedAt($createdAt)
                        ->setStartDate($startDate)
                        ->setEndDate($endDate)
                        ->setAmount($amount)
                        ->setBooker($booker)
                        ->setAd($ad)
                        ->setComment($comment);

                $manager->persist($booking);

                // Gestion des commentaires
                if(mt_rand(0,1)) {
                    $comment = new Comment();
                    $comment->setContent($faker->paragraph())
                            ->setRating(mt_rand(1,5))
                            ->setAuthor($booker)
                            ->setAd($ad);

                    $manager->persist($comment);
                }
            }

            $manager->persist($ad);
        }

        $manager->flush();
    }
}
