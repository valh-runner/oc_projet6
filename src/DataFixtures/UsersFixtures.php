<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Entity\Trick;
use App\Entity\User;
use App\Service\FileHelper;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * This fixture load an example set of generic users having commented initial tricks
 */
class UsersFixtures extends Fixture implements ContainerAwareInterface
{
	private $container;

	public function __construct(UserPasswordEncoderInterface $encoder)
	{
	    $this->encoder = $encoder;
	}

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {

		$commentsDataset = ['Pas mal','Sympa','Cool','Qui sait faire?', 'Mortel', 'Pas évident', 'jamais sans mon casque', 'Je donne des cours particuliers sur La plagne', 'Où puis-je apprendre?', 'ça glisse!', 'tip top', 'Attention à la réception', 'Qui veut rider en groupe sur Méribel?', 'Maîtriser cette figure m\'a pris du temps'];

    	$faker = \Faker\Factory::create('fr_FR');
		$fileHelper = new FileHelper();
        $filesystem = new Filesystem();

    	// Create 25 fake users who commented every tricks
    	for($i = 1; $i <= 25; $i++){
    		$user = new User();
    		$username = $faker->userName();
    		$hash = $this->encoder->encodePassword($user, $username);

    		// récupération firstUser grâce à son username
    		$firstUser = $this->getReference('admin-user');

			$daysSinceFirstUser = (new \DateTime())->diff($firstUser->getCreationMoment())->days;
			$userCreationDate = $faker->dateTimeBetween('- '.$daysSinceFirstUser.' days');

			$newFilename = null;
			// Set an avatar for 2 users of 3 on average
			if(mt_rand(1, 3) != 1){
				$pictureFilename =  mt_rand(870, 895).'-250x250.jpg'; // avatar file attribution

				$newFilename = $fileHelper->getUniqueFilename($pictureFilename); //filename transformation
				
	    		// Copy an avatar picture from the example set directory to uploaded images directory
		        $pathFrom = $this->container->getParameter('images_directory').'/usersAvatarSetExample/'.$pictureFilename;
		        $pathTo = $this->container->getParameter('uploaded_img_directory').'/'.$newFilename;
		        $filesystem->copy($pathFrom, $pathTo, true);
	   		}

    		$user->setUsername($username)
    			 ->setEmail($username.'@'.$faker->safeEmailDomain())
    			 ->setPassword($hash)
    			 ->setConfirmed(1)
    			 ->setCreationMoment($userCreationDate)
    			 ->setPictureFilename($newFilename);
    		$manager->persist($user);

			$tricks = $manager->getRepository(Trick::class)->findAll();

			foreach($tricks as $trick){
		    	// Create between 0 and 2 fake comment by user, so one by user in average
	        	for($m = 1; $m <= mt_rand(0, 2); $m++){
	        		$daysSinceUserExist = (new \DateTime())->diff($userCreationDate)->days;
	        		$commentContent = $commentsDataset[mt_rand(0, count($commentsDataset)-1)]; //random content
	        		$comment = new Comment();
	        		$comment->setContent($commentContent)
	        				->setCreationMoment($faker->dateTimeBetween('-'.$daysSinceUserExist.' days'))
	        				->setUser($user)
	        				->setTrick($trick);
	        		$manager->persist($comment);
	        	}
			}
    	}

        $manager->flush();
    }
}
