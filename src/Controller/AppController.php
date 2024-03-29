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
use App\Service\FileHelper;
use App\Service\SlugGenerator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Application functionalities
 */
class AppController extends AbstractController
{
    /**
     * Homepage
     * @param TrickRepository $repo 
     * @return string
     * 
     * @Route("/", name="home")
     */
    public function index(TrickRepository $repo)
    {
        $tricks = $repo->findBy(array(), null, 4);

        return $this->render('app/index.html.twig', [
            'controller_name' => 'AppController',
            'tricks' => $tricks
        ]);
    }

    /**
     * Trick details page
     * @param string $slug 
     * @param TrickRepository $repo 
     * @param Request $request 
     * @param EntityManagerInterface $manager 
     * @return string
     * 
     * @Route("/details_trick/{slug}", name="show")
     */
    public function show(string $slug, TrickRepository $repo, Request $request, EntityManagerInterface $manager)
    {
        $trick = $repo->findOneBy(['slug' => $slug]); // getting the trick by slug
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
            return $this->redirectToRoute('show', ['slug' => $trick->getSlug()]);
        }
        
        return $this->render('app/show.html.twig', [
            'trick' => $trick,
            'commentForm' => $form->createView(),
            'comments' => $comments
        ]);
    }

    /**
     * Add trick page
     * @param Request $request 
     * @param TrickRepository $repo
     * @param EntityManagerInterface $manager 
     * @param SlugGenerator $slugGenerator 
     * @return string
     *
     * @Route("/creation_trick", name="add_trick")
     */
    public function addTrick(Request $request, TrickRepository $repo, EntityManagerInterface $manager, SlugGenerator $slugGenerator)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY'); //restrict access to connected users
        $user = $this->getUser();

        $trick = new Trick();

        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()){

            $catTypeValue = $form->get('categoryType')->getData();
            $slug = $slugGenerator->convert( $trick->getName() );

            // if category definition mode choose is unknown 
            if($catTypeValue != 1 && $catTypeValue != 2){
                $this->addFlash('danger', 'choix inconnu du mode de définition de la catégorie');
            }
            // if generated slug already exists
            elseif( $repo->findOneBy(['slug' => $slug]) !== null){
                $this->addFlash('danger', 'le slug correspondant à ce titre existe déjà');
            }
            else{
                $trick->setCreationMoment(new \DateTime())
                      ->setUser($user)
                      ->setSlug($slug); // slug converted trick's name

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
     * Update trick page
     * @param string $slug 
     * @param TrickRepository $repo 
     * @param Request $request 
     * @param EntityManagerInterface $manager 
     * @param SlugGenerator $slugGenerator 
     * @return string
     * 
     * @Route("/modification_trick/{slug}", name="update_trick")
     */
    public function updateTrick(string $slug, TrickRepository $repo, Request $request, EntityManagerInterface $manager, SlugGenerator $slugGenerator)
    {
        $trick = $repo->findOneBy(['slug' => $slug]); // getting the trick by slug

        $this->denyAccessUnlessGranted('edit', $trick); //restrict edition access to trick owner user and admin user
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
            $newSlug = $slugGenerator->convert( $form->get('name')->getData() );

            // if category definition mode choose is unknown 
            if($catTypeValue != 1 && $catTypeValue != 2){
                $this->addFlash('danger', 'choix inconnu du mode de définition de la catégorie');
            }
            // if slug updated and the new one already exists
            elseif($newSlug !== $slug && $repo->findOneBy(['slug' => $newSlug]) !== null  ){
                $this->addFlash('danger', 'le slug correspondant à ce titre existe déjà');
            }
            else {
                $trick->setRevisionMoment(new \DateTime())
                      ->setSlug($slug); // slug converted trick's name

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

        }

        // Default form presets
        $form->get('categoryType')->setData(1); // check radio button because a trick have always an existant category
        $form->get('existantCategory')->setData($oTrickCategory); // preselect the current category

        return $this->render('app/add_trick.html.twig', [
            'formTrick' => $form->createView(),
            'editMode' => true,
            'pageTitle' => 'Modification d\'un trick',
            'trick' => $trick,
        ]);
    }

    /**
     * Delete trick page
     * @param Trick $trick 
     * @param EntityManagerInterface $manager 
     * @return string
     * 
     * @Route("/suppression_trick/{id}", name="delete_trick")
     */
    public function deleteTrick(Trick $trick, EntityManagerInterface $manager)
    {
        $this->denyAccessUnlessGranted('delete', $trick); //restrict deletion access to trick owner user and admin user

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
     * Manage account page
     * @param Request $request 
     * @param EntityManagerInterface $manager 
     * @return string
     * 
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
            $accountPictureDelete = $form->get('pictureDeletionState')->getData();
            if($accountPictureDelete == 'true'){
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
     * Save as file the passed uploaded file value
     * @param File $file 
     * @return string $newFilename
     */
    public function saveUploadedFile(File $file)
    {
        $fileHelper = new FileHelper();
        $newFilename = $fileHelper->getUniqueFilename($file->getClientOriginalName()); //filename transformation
        try {
            // Move the file to the uploaded images directory
            $file->move( $this->getParameter('uploaded_img_directory') , $newFilename );
        } catch (FileException $e) {
            throw $e; // handle exception if something happens during file upload
        }

        return $newFilename;
    }

    /**
     * Delete the uploaded file so named
     * @param string $filename 
     * @return string $newFilename
     */
    public function deleteUploadedFile(string $filename)
    {
        $filesystem = new Filesystem();
        $path = $this->getParameter('uploaded_img_directory').'/'.$filename;
        $filesystem->remove($path);
    }
}
