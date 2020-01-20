<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Picture;
use App\Entity\Trick;
use App\Entity\Video;
use App\Form\CommentType;
use App\Form\TrickType;
use App\Repository\TrickRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(TrickRepository $repo)
    {
        $tricks = $repo->findAll();
        /*$tricks = $repo->findBy(
            [],
            ['creationMoment' => 'DESC']
        );*/

        /*$tricks = $repo->createQueryBuilder('e')
                       ->select('e')
                       ->orderBy('e.creation_moment', 'DESC')
                       ->setMaxResults(100);*/

        return $this->render('app/index.html.twig', [
            'controller_name' => 'AppController',
            'tricks' => $tricks
        ]);
    }

    /**
     * @Route("/show/{id}", name="show")
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
     * @Route("/ajout_trick", name="add_trick")
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

                // submitted pictures handling
                $submittedPictures = $trick->getPictures();
                foreach($submittedPictures as $submittedPicture){
                    /** @var UploadedFile $file */
                    $file = $submittedPicture->getFile();

                    // if file uploaded, because field not required
                    if ($file) {
                        //filename transformations
                        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME); // filename of submitted image
                        $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename); // reformated filename
                        $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension(); // unique reformated filename

                        try {
                            $file->move( $this->getParameter('images_directory') , $newFilename ); // Move the file to the directory where images are stored
                        } catch (FileException $e) {
                            throw $e; // handle exception if something happens during file upload
                        }

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
            'formTrick' => $form->createView()
        ]);
    }
}
