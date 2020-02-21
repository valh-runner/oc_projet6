<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Repository\CommentRepository;
use App\Repository\TrickRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SliceController extends AbstractController
{
    /**
     * @Route("/charger_plus/{number}/{offset}", name="load_more")
     */
    public function loadMore(int $number, int $offset, TrickRepository $repo)
    {
    	$tricks = $repo->findBy(array(), null, $number, $offset);

        return $this->render('slice/load_more.html.twig', [
            'controller_name' => 'SliceController',
            'tricks' => $tricks,
        ]);
    }

    /**
     * @Route("/charger_nombre_commentaires/{trick}", name="load_comments_count")
     */
    public function loadCommentsCount(Trick $trick, CommentRepository $repo)
    {
        $commentsNumber = $trick->getComments()->count();

        $data = ['CommentsCount' => $commentsNumber ];

        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/charger_page_commentaires/{trick}/{offset}", name="load_comments_page")
     */
    public function loadCommentsPage(Trick $trick, int $offset, TrickRepository $repo)
    {
        $comments = $trick->getCommentsSlice($offset, 10);

        return $this->render('slice/load_comments_page.html.twig', [
            'comments' => $comments,
        ]);
    }

}
