<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Picture;
use App\Entity\Trick;
use App\Entity\User;
use App\Entity\Video;
use App\Service\SlugGenerator;
use App\Service\FileHelper;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * This fixture load initial tricks of the application
 */
class AppFixtures extends Fixture implements ContainerAwareInterface
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

		$tricksList = array();
		$slugGenerator = new SlugGenerator();
		$fileHelper = new FileHelper();
        $filesystem = new Filesystem();

    	// Create first user
		$firstUser = new User();
		$username = 'generator';
		$hash = $this->encoder->encodePassword($firstUser, $username);
		$dateCreationUser = (new \DateTime())->sub(new \DateInterval('P30D')); //one month ago

		$pictureFilename = '869-250x250.jpg';

        $newFilename = $fileHelper->getUniqueFilename($pictureFilename); //filename transformation
        
		// Copy an avatar picture from the example set directory to uploaded images directory
        $pathFrom = $this->container->getParameter('images_directory').'/usersAvatarSetExample/'.$pictureFilename;
        $pathTo = $this->container->getParameter('uploaded_img_directory').'/'.$newFilename;
        $filesystem->copy($pathFrom, $pathTo, true);

		$firstUser->setUsername($username)
			->setEmail('generator@snowtricks.fr')
			->setPassword($hash)
			->setConfirmed(1)
			->setCreationMoment($dateCreationUser)
			->setPictureFilename($newFilename)
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
		    		$trick->setName($trickData[0])
		    			  ->setDescription(ucfirst($trickData[2]).'.')
		    			  ->setCreationMoment($firstUser->getCreationMoment()) // same of first user creation
		    			  ->setUser($firstUser)
		    			  ->setCategory($category)
		    			  ->setSlug( $slugGenerator->convert($trickData[0]) ); // slug converted trick's name
		    		$tricksList[] = $trick;
		    		$manager->persist($trick);

			    	// Create pictures
			    	$nbrPictures = count($trickData[4]);
		        	for($n = 0; $n < $nbrPictures; $n++){
		        		$pictureFilename = $trickData[4][$n];

				        $newFilename = $fileHelper->getUniqueFilename($pictureFilename); //filename transformation
				        
		        		// Copy a trick picture from the inital set directory to uploaded images directory
				        $pathFrom = $this->container->getParameter('images_directory').'/initialSet/'.$pictureFilename;
				        $pathTo = $this->container->getParameter('uploaded_img_directory').'/'.$newFilename;
				        $filesystem->copy($pathFrom, $pathTo, true);

		        		$picture = new Picture();
		        		$picture->setFilename($newFilename)
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

    	$manager->flush();

    	// share admin user object with other fixtures
        $this->addReference('admin-user', $firstUser);
    }
}
