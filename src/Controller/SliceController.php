<?php

namespace App\Controller;

use App\Repository\TrickRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class SliceController extends AbstractController
{
    /**
     * @Route("/charger_plus/{offset}", name="load_more")
     */
    public function loadMore(int $offset, TrickRepository $repo)
    {


    	$tricks = $repo->findBy(array(), null, 8, $offset);

        //$tricks = $repo->findTenFromNumber(3);

        return $this->render('slice/load_more.html.twig', [
            'controller_name' => 'SliceController',
            'tricks' => $tricks,
        ]);
    }
}
