<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Picture;
use App\Entity\Trick;
use App\Entity\Video;
use App\Form\AccountType;
use App\Form\CommentType;
use App\Form\TrickType;
use App\Repository\TrickRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(TrickRepository $repo)
    {
        //$tricks = $repo->findAll();
        $tricks = $repo->findBy(array(), null, 8);

        return $this->render('app/index.html.twig', [
            'controller_name' => 'AppController',
            'tricks' => $tricks
        ]);
    }

    /**
     * @Route("/details_trick/{id}", name="show")
     */
    public function show(Trick $trick, Request $request, EntityManagerInterface $manager)
    {
        
        $comments = $trick->getComments();

        $newComment = new Comment();
        $form = $this->createForm(CommentType::class, $newComment);
        
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()){
            $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
            $user = $this->getUser();

            $newComment->setCreationMoment(new \DateTime())
                    ->setUser($user)
                    ->setTrick($trick);

            $manager->persist($newComment);
            $manager->flush();
            return $this->redirectToRoute('show', ['id' => $trick->getId()]);
        }
        
        return $this->render('app/show.html.twig', [
            'trick' => $trick,
            'commentForm' => $form->createView(),
            'comments' => $comments
        ]);
    }

    /**
     * @Route("/creation_trick", name="add_trick")
     */
    public function addTrick(Request $request, EntityManagerInterface $manager)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY'); //restrict access to connected users
        $user = $this->getUser();

        $trick = new Trick();

        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()){

            $catTypeValue = $form->get('categoryType')->getData();

            // if category definition mode choose is unknown 
            if($catTypeValue != 1 && $catTypeValue != 2){
                $this->addFlash('danger', 'choix inconnu du mode de définition de la catégorie');
            }
            else{
                $trick->setCreationMoment(new \DateTime())
                      ->setUser($user);

                // category assignment depending on whether the category is existing or new
                switch ($catTypeValue) {
                    case 1: // existing category
                        $oExistantCat = $form->get('existantCategory')->getData();
                        $trick->setCategory($oExistantCat);
                        break;
                    case 2: // new category
                        $newCatName = $form->get('newCategory')->getData();
                        // category creation
                        $category = new Category();
                        $category->setName($newCatName);
                        $manager->persist($category);

                        $trick->setCategory($category);
                        break;
                }

                // submitted main picture handling
                $uploadedFile = $form->get('featuredPicture')->getData();
                // if file uploaded, because field not required
                if ($uploadedFile) {
                    $newUploadedFilename = $this->saveUploadedFile($uploadedFile);
                    $trick->setMainPictureFilename($newUploadedFilename);
                }

                // submitted pictures handling
                $submittedPictures = $trick->getPictures();
                foreach($submittedPictures as $submittedPicture){
                    /** @var UploadedFile $file */
                    $file = $submittedPicture->getFile();

                    // if file uploaded, because field not required
                    if ($file) {
                        $newFilename = $this->saveUploadedFile($file);
                        $submittedPicture->setFilename($newFilename); // store only the filename in database
                        $manager->persist($submittedPicture);
                    }
                }

                $manager->persist($trick);
                $manager->flush();
                $this->addFlash('success', 'la figure a bien été enregistrée');
            }

            return $this->redirectToRoute('home');
        }

        return $this->render('app/add_trick.html.twig', [
            'formTrick' => $form->createView(),
            'editMode' => false,
            'pageTitle' => 'Création d\'un trick'
        ]);
    }

    /**
     * @Route("/modification_trick/{id}", name="update_trick")
     */
    public function updateTrick(Trick $trick, Request $request, EntityManagerInterface $manager)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY'); //restrict access to connected users
        $user = $this->getUser();

        // Store initial pictures of the trick to compare
        $originalPictures = new ArrayCollection();
        foreach ($trick->getPictures() as $picture) {
            $originalPictures->add($picture);
        }
        // Store initial videos of the trick to compare
        $originalVideos = new ArrayCollection();
        foreach ($trick->getVideos() as $video) {
            $originalVideos->add($video);
        }
        // Store initial category of the trick to compare
        $oTrickCategory = $trick->getCategory();

        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){

            $catTypeValue = $form->get('categoryType')->getData();

            // if category definition mode choose is unknown 
            if($catTypeValue != 1 && $catTypeValue != 2){
                $this->addFlash('danger', 'choix inconnu du mode de définition de la catégorie');
            }
            else {
                $trick->setRevisionMoment(new \DateTime());

                // category assignment depending on whether the category is existing or new
                switch ($catTypeValue) {
                    case 1: // existing category
                        $oExistantCat = $form->get('existantCategory')->getData();
                        if($oExistantCat != $oTrickCategory){ // if category changed
                            $trick->setCategory($oExistantCat); // set category of the trick
                        }
                        break;
                    case 2: // new category
                        $newCatName = $form->get('newCategory')->getData();
                        // category creation
                        $category = new Category();
                        $category->setName($newCatName);
                        $manager->persist($category);
                        $trick->setCategory($category); // set category of the trick
                        break;
                }

                // submitted hidden field about main picture deletion state
                $featuredPictureDeletionState = $form->get('featuredPictureDeletionState')->getData();
                if($featuredPictureDeletionState == 'true'){
                    $trick->setMainPictureFilename(null);
                }

                // submitted main picture handling
                $uploadedFile = $form->get('featuredPicture')->getData();
                // if file uploaded, because field not required
                if ($uploadedFile) {
                    $newUploadedFilename = $this->saveUploadedFile($uploadedFile);
                    $trick->setMainPictureFilename($newUploadedFilename);
                }

                // submitted pictures handling
                $submittedPictures = $trick->getPictures();
                foreach($submittedPictures as $submittedPicture){
                    if($submittedPicture->getId() == null){ // if new picture added - what have no id
                        /** @var UploadedFile $file */
                        $file = $submittedPicture->getFile();
                        if ($file) { // if file uploaded
                            $newFilename = $this->saveUploadedFile($file);
                            
                            $submittedPicture->setFilename($newFilename); // store only the filename in database
                            $manager->persist($submittedPicture);
                        }
                    }
                }

                // check for removed Pictures
                foreach ($originalPictures as $picture) {
                    // if the picture is missing in submission
                    if (false === $trick->getPictures()->contains($picture)) {
                        $picture->setTrick(null); // remove the relationship from the relashionship owner - the picture
                        $manager->persist($picture);
                        $manager->remove($picture); // delete the orphan Picture from database
                        $this->deleteUploadedFile($picture->getFilename()); // delete the orphan Picture from files
                    }
                }

                // check for removed Videos
                foreach ($originalVideos as $video) {
                    if (false === $trick->getVideos()->contains($video)) {
                        $video->setTrick(null); // remove the relationship from the relashionship owner - the video
                        $manager->persist($video);
                        $manager->remove($video); // delete the orphan Video
                    }
                }

                $manager->persist($trick);
                $manager->flush();
                $this->addFlash('success', 'la figure a bien été enregistrée');
            }

            return $this->redirectToRoute('home');

        }else{
            $form->get('categoryType')->setData(1); // check radio button because a trick have always an existant category
            $form->get('existantCategory')->setData($oTrickCategory); // preselect the current category
        }

        return $this->render('app/add_trick.html.twig', [
            'formTrick' => $form->createView(),
            'editMode' => true,
            'pageTitle' => 'Modification d\'un trick',
            'trick' => $trick,
        ]);
    }

    /**
     * @Route("/suppression_trick/{id}", name="delete_trick")
     */
    public function deleteTrick(Trick $trick, EntityManagerInterface $manager)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY'); //restrict access to connected users
        $user = $this->getUser();

        // remove main picture
        if($trick->getMainPictureFilename() != null){
            $this->deleteUploadedFile($trick->getMainPictureFilename()); //delete main picture from files
        }

        // remove Pictures
        foreach ($trick->getPictures() as $picture) {
            $trick->removePicture($picture);
            $manager->remove($picture); // delete the orphan Picture from database
            $this->deleteUploadedFile($picture->getFilename()); // delete the orphan Picture from files
        }
        // remove Videos
        foreach ($trick->getVideos() as $video) {
            $trick->removeVideo($video);
            $manager->remove($video); // delete the orphan Video
        }
        // remove Comments
        foreach ($trick->getComments() as $comment) {
            $trick->removeComment($comment);
            $manager->remove($comment); // delete the orphan Comment
        }

        $manager->remove($trick);
        $manager->flush();
        $this->addFlash('success', 'le trick a bien été supprimé');

        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/gestion_compte", name="manage_account")
     */
    public function manageAccount(Request $request, EntityManagerInterface $manager)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY'); //restrict access to connected users
        $user = $this->getUser();

        $form = $this->createForm(AccountType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){

            // submitted hidden field about account picture deletion state
            $accountPictureDeletionState = $form->get('pictureDeletionState')->getData();
            if($accountPictureDeletionState == 'true'){
                $user->setPictureFilename(null);
            }

            // submitted account picture handling
            $uploadedFile = $form->get('accountPicture')->getData();
            // if file uploaded, because field not required
            if ($uploadedFile) {
                $newUploadedFilename = $this->saveUploadedFile($uploadedFile);
                $user->setPictureFilename($newUploadedFilename);
            }

            $manager->flush();
            $this->addFlash('success', 'La modification de l\'image du compte a bien été prise en compte');
            return $this->redirectToRoute('home');
        }

        return $this->render('app/manage_account.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    /**
     * 
     */
    public function saveUploadedFile($file)
    {
        //filename transformations
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME); // filename of submitted image
        $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename); // reformated filename
        $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension(); // unique reformated filename
        try {
            $file->move( $this->getParameter('images_directory') , $newFilename ); // Move the file to the uploaded images directory
        } catch (FileException $e) {
            throw $e; // handle exception if something happens during file upload
        }

        return $newFilename;
    }

    /**
     * 
     */
    public function deleteUploadedFile($filename)
    {
        $filesystem = new Filesystem();
        $path = $this->getParameter('images_directory').'/'.$filename;
        $result = $filesystem->remove($path);
        if ($result === false) {
            throw new \Exception(sprintf('Error deleting "%s"', $path));
        }
    }
}
