<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Picture;
use App\Entity\Trick;
use App\Entity\User;
use App\Entity\Video;
use App\Service\SlugGenerator;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
	public function __construct(UserPasswordEncoderInterface $encoder)
	{
	    $this->encoder = $encoder;
	}

    public function load(ObjectManager $manager)
    {
    	$categoriesDataset = ['Rotations','Flips','Grabs','Slides'];

		$tricksDataset = [
			[
				'1080',0,'rotation horizontale de trois tours complets',
				['j4NfAsszIOk','camHB0Rj4gA'],
				['1080-1.jpg','1080-2.jpg','1080-3.jpg']
			],
			[
				'180',0,'rotation horizontale d\'un demi tour',
				['ATMiAVTLsuc','JMS2PGAFMcE	GnYAlEt-s00'],
				['180-1.jpg','180-2.jpg','180-3.jpg']
			],
			[
				'360',0,'rotation horizontale d\'un tours complet',
				['hUddT6FGCws','GS9MMT_bNn8','_rS2i4-yb6E'],
				['360-1.jpg','360-2.jpg','360-3.jpg']
			],
			[
				'back flip',1,'rotation verticale en arrière',
				['AMsWP9WJS_0','SlhGVnFPTDE','vIqaebj-GNw'],
				['backflip-1.jpg','backflip-2.jpg','backflip-3.jpg']
			],
			[
				'front flip',1,'rotation verticale en avant',
				['xhvqu2XBvI0','eGJ8keB1-JM','aTTkQ45DUfk'],
				['frontflip-1.jpg','frontflip-2.jpg','frontflip-3.jpg']
			],
			[
				'indy',2,'saisie de la carre frontside de la planche, entre les deux pieds, avec la main arrière',
				['iKkhKekZNQ8','6yA3XqjTh_w'],
				['indy-1.jpg','indy-2.jpg','indy-3.jpg','indy-4.jpg']
			],
			[
				'mute',2,'saisie de la carre frontside de la planche entre les deux pieds avec la main avant',
				['4sha5smEUHA','KXDQv7f8JNs','8r_yZfBWCeQ'],
				['mute-1.jpg','mute-2.jpg','mute-3.jpg','mute-4.jpg','mute-5.jpg']
			],
			[
				'sad',2,'saisie de la carre backside de la planche, entre les deux pieds, avec la main avant',
				['KEdFwJ4SWq4'],
				['sad-1.jpg','sad-2.jpg','sad-3.jpg']
			],
			[
				'nose slide',3,'glissade avec planche perpendiculaire à la barre de slide avec la barre du coté avant de la planche',
				['7AB0FZWyrGQ','Iw77dvnNSKk','oAK9mK7wWvw'],
				['noseslide-1.jpg','noseslide-2.jpg','noseslide-3.jpg']
			],
			[
				'tail slide',3,'glissade avec planche perpendiculaire à la barre de slide avec la barre du coté arrière de la planche',
				['h_jU7vjmLjU','HRNXjMBakwM','inAxMRSlGS8'],
				['tailslide-1.jpg','tailslide-2.jpg','tailslide-3.jpg']
			]
		];

		$commentsDataset = ['Pas mal','Sympa','Cool','Qui sait faire?', 'Mortel', 'Pas évident', 'jamais sans mon casque', 'Je donne des cours particuliers sur à la plagne', 'Où puis-je apprendre?', 'ça glisse!', 'tip top', 'Attention à la réception', 'Qui veut rider en groupe sur Méribel?'];

		$faker = \Faker\Factory::create('fr_FR');
		$incrementedPictureSeed = 1;
		$tricksList = array();
		$slugGenerator = new SlugGenerator();

    	// Create first user
		$firstUser = new User();
		$username = 'generator';
		$hash = $this->encoder->encodePassword($firstUser, $username);
		$dateCreationUser = (new \DateTime())->sub(new \DateInterval('P30D')); //one month ago
		//$dateCreationUser = date('d-m-Y', strtotime('-30 day', strtotime(new \Datetime()) ));
		$firstUser->setUsername($username)
			->setEmail('generator@snowtricks.fr')
			->setPassword($hash)
			->setConfirmed(1)
			//->setCreationMoment($faker->dateTimeBetween('- 30 days', '- 27 days'));
			->setCreationMoment($dateCreationUser)
			->setPictureFilename('869-250x250.jpg')
			->setRoles(['ROLE_ADMIN']);
		$manager->persist($firstUser);

    	// Create categories
    	$nbrCategories = count($categoriesDataset);
    	for($j = 0; $j < $nbrCategories; $j++){
    		$category = new Category();
    		$category->setName($categoriesDataset[$j]);
    		$manager->persist($category);

	    	// Create tricks
	    	$nbrTricks = count($tricksDataset);
	    	for($k = 0; $k < $nbrTricks; $k++){
	    		$trickData = $tricksDataset[$k];

	    		//If the trick correspond to the current category
	    		if($trickData[1] == $j){
		    		$trick = new Trick();
		        	//$daysSinceUser = (new \DateTime())->diff($firstUser->getCreationMoment())->days;
		    		$trick->setName($trickData[0])
		    			  ->setDescription(ucfirst($trickData[2]).'.')
		    			  //->setCreationMoment($faker->dateTimeBetween('-'.$daysSinceUser.' days'))
		    			  ->setCreationMoment($firstUser->getCreationMoment()) // same of first user creation
		    			  ->setUser($firstUser)
		    			  ->setCategory($category)
		    			  ->setSlug( $slugGenerator->convert($trickData[0]) ); // slug converted trick's name
		    		$tricksList[] = $trick;
		    		$manager->persist($trick);

			    	// Create pictures
			    	$nbrPictures = count($trickData[4]);
		        	for($n = 0; $n < $nbrPictures; $n++){
		        		$pictureData = $trickData[4][$n];

		        		$picture = new Picture();
		        		$picture->setFilename($pictureData)
		        				->setTrick($trick);
		        		$manager->persist($picture);
		        	}

			    	// Create videos
		        	foreach($trickData[3] as $embedLinkCode){
		        		$video = new Video();
		        		$video->setEmbedLink('https://www.youtube.com/embed/'.$embedLinkCode)
		        			  ->setTrick($trick);
		        		$manager->persist($video);
		        	}

		    	}

	    	}
    	}

    	// Create 25 fake users
    	for($i = 1; $i <= 25; $i++){
    		$user = new User();
    		$username = $faker->userName();
    		$hash = $this->encoder->encodePassword($user, $username);

			$daysSinceFirstUser = (new \DateTime())->diff($firstUser->getCreationMoment())->days;
			$userCreationDate = $faker->dateTimeBetween('- '.$daysSinceFirstUser.' days');
			$pictureFilename =  mt_rand(1, 3) == 1 ? null : mt_rand(870, 894).'-250x250.jpg';

    		$user->setUsername($username)
    			 ->setEmail($username.'@'.$faker->safeEmailDomain())
    			 ->setPassword($hash)
    			 ->setConfirmed(1)
    			 ->setCreationMoment($userCreationDate)
    			 ->setPictureFilename($pictureFilename);
    		$manager->persist($user);

			foreach($tricksList as $trick){
		    	// Create between 0 and 2 fake comment by user, so one by user in average
	        	for($m = 1; $m <= mt_rand(0, 2); $m++){
	        		$daysSinceUserCreation = (new \DateTime())->diff($userCreationDate)->days;
	        		$commentContent = $commentsDataset[mt_rand(0, count($commentsDataset)-1)]; //random content
	        		$comment = new Comment();
	        		$comment->setContent($commentContent)
	        				->setCreationMoment($faker->dateTimeBetween('-'.$daysSinceUserCreation.' days'))
	        				->setUser($user)
	        				->setTrick($trick);
	        		$manager->persist($comment);
	        	}
			}
    	}
    	
        $manager->flush();
    }
}
