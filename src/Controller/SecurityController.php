<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Security functionalities
 */
class SecurityController extends AbstractController
{
    /**
     * Registration page
     * @param Request $request 
     * @param EntityManagerInterface $manager 
     * @param UserPasswordEncoderInterface $encoder 
     * @param TokenGeneratorInterface $tokenGenerator 
     * @param MailerInterface $mailer 
     * @return string
     * 
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
    		$user->setPassword($hash)
                 ->setConfirmed(0)
                 ->setToken($token)
                 ->setCreationMoment(new \DateTime());

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
                ->html('<h3>Bienvenue sur Snowtricks!</h3>
                    <p>Pour finaliser votre inscription, cliquez sur le lien suivant: 
                    <a href="'.$url.'">Finaliser l\'inscription</a></p>')
            ;

            $mailer->send($email);

            $this->addFlash('success', 'Votre inscription a été prise en compte. Pour la finaliser, suivez le lien qui viens de vous être envoyé par e-mail.');
    		return $this->redirectToRoute('home');
    	}

        return $this->render('security/registration.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Registration confirm page
     * @param Request $request 
     * @param EntityManagerInterface $manager 
     * @return string
     * 
     * @Route("/registration_confirm", name="security_registration_confirm")
     */
    public function registrationConfirm(Request $request, EntityManagerInterface $manager)
    {
        $token = $request->query->get('token');
        $success = false;
        $feedbacks = array();

        if(!$token){
            $feedbacks[] = ['danger', 'token manquant'];
        }else{
            $user = $manager->getRepository(User::class)->findOneByToken($token);

            //if no user found
            if($user == false){
                $feedbacks[] = ['danger', 'token inconnu'];
            }else{
                $hoursSinceRegistrationInit = (new \DateTime())->diff($user->getCreationMoment())->h;
                
                //if 48 hours time limit not expired
                if($hoursSinceRegistrationInit < 48){
                    $user->setToken('')
                         ->setConfirmed(1);
                    
                    $manager->persist($user);
                    $manager->flush();

                    $success = true;
                    $feedbacks[] = ['success', 'Votre inscription est finalisée'];
                }else{
                    $manager->remove($user); // user deletion
                    $manager->flush();
                    $feedbacks[] = ['danger', 'limite de temps du token expirée'];
                }
            }
        }

    	return $this->render('security/registration_confirm.html.twig', [
            'success' => $success,
            'feedbacks' => $feedbacks
        ]);
    }

    /**
     * Login page
     * @param AuthenticationUtils $authenticationUtils 
     * @return string
     * 
     * @Route("/connexion", name="security_login")
     */
    public function login(AuthenticationUtils $authenticationUtils)
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

    	return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    /**
     * Logout page
     * @return string
     * 
     * @Route("/deconnexion", name="security_logout")
     */
    public function logout() {}

    /**
     * Forgot password page
     * @param Request $request 
     * @param EntityManagerInterface $manager 
     * @param TokenGeneratorInterface $tokenGenerator 
     * @param MailerInterface $mailer 
     * @return string
     * 
     * @Route("/oubli_mot_de_passe", name="security_forgot_password")
     */
    public function forgotPassword(Request $request, EntityManagerInterface $manager, TokenGeneratorInterface $tokenGenerator, MailerInterface $mailer) {
        
        $form = $this->createFormBuilder()
                     ->add('username', TextType::class)
                     ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $usernameToFind = $form->getData()['username'];

            //search for a registered user
            $user = $manager->getRepository(User::class)->findOneBy([
                'username' => $usernameToFind,
                'confirmed' => 1
            ]);

            if($user == false){
                $this->addFlash('danger', 'aucun utilisateur correspondant');
            }else{
                // Token generation
                $token = $tokenGenerator->generateToken();
                // Database updates
                $user->setForgotPasswordMoment(new \DateTime());
                $user->setToken($token);
                $manager->flush();
                // Email send with tokenized link
                $url = $this->generateUrl('security_reset_password', array('token' => $token), UrlGeneratorInterface::ABSOLUTE_URL);
                $email = (new Email())
                    ->from('no-reply@example.com')
                    ->to($user->getEmail())
                    ->subject("Snowtricks - Réinitialisation du mot de passe")
                    ->html('<h3>Snowtricks - Mot de passe oublié</h3>
                        <p>Pour choisir un nouveau mot de passe, cliquez sur le lien suivant: 
                        <a href="'.$url.'" class="alert-link">Redéfinir le mot de passe</a></p>');
                $mailer->send($email);

                $this->addFlash('success', 'Un e-mail viens de vous être envoyé. Suivez le lien contenu dans cet e-mail pour redéfinir votre mot de passe');
                return $this->redirectToRoute('home');
            }
        }

        return $this->render('security/forgot_password.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Reset password
     * @param Request $request 
     * @param UserPasswordEncoderInterface $encoder 
     * @return string
     * 
     * @Route("/nouveau_mot_de_passe", name="security_reset_password")
     */
    public function resetPassword(Request $request, UserPasswordEncoderInterface $encoder) {

        $form = $this->createFormBuilder()
                     ->add('email', EmailType::class)
                     ->add('password', PasswordType::class)
                     ->getForm();

        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()){
            
            $formData = $form->getData();

            $manager = $this->getDoctrine()->getManager();
            //search for a registered user by email
            $user = $manager->getRepository(User::class)->findOneBy([
                'email' => $formData['email'],
                'confirmed' => 1
            ]);

            //if no user found
            if($user == false){
                $this->addFlash('danger', 'e-mail utilisateur inconnu');
            }else{
                $token = $request->query->get('token');
                // if missing token
                if(!$token){
                    $this->addFlash('danger', 'token manquant');
                }else{
                    // if wrong token
                    if($user->getToken() != $token){
                        $this->addFlash('danger', 'token non valide');
                    }else{
                        $hrsSinceForgotPsswd = (new \DateTime())->diff($user->getForgotPasswordMoment())->h;
                        //if 48 hours time limit not expired since forgot password ask
                        if($hrsSinceForgotPsswd > 48){
                            $this->addFlash('danger', 'limite de temps du token expirée');
                        }else{
                            $hash = $encoder->encodePassword($user, $formData['password']);
                            $user->setPassword($hash)
                                 ->setToken('');
                            
                            $manager->persist($user);
                            $manager->flush();

                            $this->addFlash('success', 'le changement de mot de passe a bien été pris en compte');
                        }
                    }
                }

                return $this->redirectToRoute('home');
            }
        }

        return $this->render('security/reset_password.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
