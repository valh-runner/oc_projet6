<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/security", name="security")
     */
    public function index()
    {
        return $this->render('security/index.html.twig', [
            'controller_name' => 'SecurityController',
        ]);
    }

    /**
     * @Route("/registration", name="security_registration")
     */
    public function registration(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder)
    {
    	$user = new User();

    	$form = $this->createForm(RegistrationType::class, $user);

    	$form->handleRequest($request);
    	
    	if($form->isSubmitted() && $form->isValid()){
    		$hash = $encoder->encodePassword($user, $user->getPassword());
    		$user->setPassword($hash);

    		$manager->persist($user);
    		$manager->flush();

    		return $this->redirectToRoute('registration_confirm');
    	}

        return $this->render('security/registration.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/registration_confirm", name="security_registration_confirm")
     */
    public function registrationConfirm()
    {
    	return $this->render('security/registration_confirm.html.twig');
    }

    /**
     * @Route("/connexion", name="security_login")
     */
    public function login()
    {
    	return $this->render('security/login.html.twig');
    }

    /**
     * @Route("/deconnexion", name="security_logout")
     */
    public function logout() {}
}
