<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Trick;
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
}
