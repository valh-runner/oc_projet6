<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Repository\TrickRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(TrickRepository $repo)
    {
    	$tricks = $repo->findAll();

        return $this->render('app/index.html.twig', [
            'controller_name' => 'AppController',
            'tricks' => $tricks
        ]);
    }

    /**
     * @Route("/show/{id}", name="show")
     */
    //public function show(Article $article, Request $request, EntityManagerInterface $manager)
    public function show(Trick $trick)
    {
        return $this->render('app/show.html.twig', [
            'trick' => $trick
            //'commentForm' => $form->createView()
        ]);
    }
}
