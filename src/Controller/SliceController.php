<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Repository\CommentRepository;
use App\Repository\TrickRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Asynchronous functionalities
 */
class SliceController extends AbstractController
{
    /**
     * Load a number of tricks as page part, starting from the offset
     * @param int $number 
     * @param int $offset 
     * @param TrickRepository $repo 
     * @return string
     * 
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
     * Load the comments number of a trick as JSON
     * @param Trick $trick 
     * @param CommentRepository $repo 
     * @return Response $response
     * 
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
     * Load ten comments of a trick as page part, starting from the offset
     * @param Trick $trick 
     * @param int $offset 
     * @param TrickRepository $repo 
     * @return string
     * 
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
