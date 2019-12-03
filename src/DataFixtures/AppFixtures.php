<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Trick;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
    	$faker = \Faker\Factory::create('fr_FR');

    	// Create 5 fake users
    	for($i = 1; $i <= 5; $i++){
    		$user = new User();
    		$user->setUsername($faker->userName())
    			 ->setEmail($faker->safeEmail())
    			 ->setPassword($faker->userName());
    		$manager->persist($user);

	    	// Create 3 fake category
	    	for($j = 1; $j <= 3; $j++){
	    		$category = new Category();
	    		$category->setName($faker->country());
	    		$manager->persist($category);

		    	// Create 3 fake tricks
		    	for($k = 1; $k <= 3; $k++){
		    		$trick = new Trick();
		        	$description = '<p>' . join($faker->paragraphs(2), '</p><p>') . '</p>';

		    		$trick->setName($faker->city())
		    			  ->setDescription($description)
		    			  ->setCreationMoment($faker->dateTimeBetween('- 3 months'))
		    			  //->setRevisionMoment()
		    			  ->setUser($user)
		    			  ->setCategory($category);
		    		$manager->persist($trick);

			    	// Create between 10 and 20 fake comments
		        	for($m = 1; $m <= mt_rand(5, 10); $m++){
		        		$comment = new Comment();
		        		$days = (new \DateTime())->diff($trick->getCreationMoment())->days;

		        		$comment->setContent($faker->sentence())
		        				->setCreationMoment($faker->dateTimeBetween('-' . $days . ' days'))
		        				->setUser($user)
		        				->setTrick($trick);
		        		$manager->persist($comment);
		        	}
		    	}
	    	}
    	}

        $manager->flush();
    }
}
