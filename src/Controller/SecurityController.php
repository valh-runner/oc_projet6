<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

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
    public function registration(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder, TokenGeneratorInterface $tokenGenerator, MailerInterface $mailer)
    {
    	$user = new User();

    	$form = $this->createForm(RegistrationType::class, $user);

    	$form->handleRequest($request);
    	
    	if($form->isSubmitted() && $form->isValid()){
    		$hash = $encoder->encodePassword($user, $user->getPassword());
            $token = $tokenGenerator->generateToken();
    		$user->setPassword($hash);
            $user->setConfirmed(0);
            $user->setToken($token);

    		$manager->persist($user);
    		$manager->flush();

            $url = $this->generateUrl('security_registration_confirm', 
                array('token' => $token),
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $email = (new Email())
                ->from('no-reply@example.com')
                ->to($user->getEmail())
                ->subject("Snowtricks - Finalisation de l'inscription")
                ->html('<p><a href="'.$url.'">Finaliser l\'inscription</a></p>')
            ;

            $mailer->send($email);

            $this->addFlash('notice', 'Votre inscription a été prise en compte. Pour la finaliser, suivez le lien qui viens de vous être envoyé par e-mail.');
    		return $this->redirectToRoute('home');
    	}

        return $this->render('security/registration.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/registration_confirm/{token}", name="security_registration_confirm")
     */
    public function registrationConfirm(string $token, EntityManagerInterface $manager, TokenGeneratorInterface $tokenGenerator)
    {
        $user = $manager->getRepository(User::class)->findOneByToken($token);

        if($user == false){
            $success = false;
        }else{
            $user->setToken('');
            $user->setConfirmed(1);
            
            $manager->persist($user);
            $manager->flush();

            $success = true;
        }

    	return $this->render('security/registration_confirm.html.twig', ['success' => $success]);
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
