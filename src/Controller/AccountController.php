<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AccountType;
use App\Entity\PasswordUpdate;
use App\Form\RegistrationType;
use App\Form\PasswordUpdateType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AccountController extends AbstractController
{
    /**
     * Permet de se connecter
     * @Route("/login", name="account_login")
     * @param AuthenticationUtils $utils
     * @return Response
     */
    public function login(AuthenticationUtils $utils)
    {
        
        return $this->render('account/login.html.twig',[
            'hasError' => $utils->getLastAuthenticationError() !== null,
            'username' => $utils->getLastUsername()
        ]);
    }

    /**
     * Permet de se deconnecter
     * @Route("/logout", name="account_logout")
     * @return void
     */
    public function logout() {}

    /**
     * Permet de s'inscrire
     * @Route("/register", name="account_register")
     * @param Request $request
     * @param ObjectManager $manager
     * @param UserPasswordEncoderInterface $encoder
     * @return Response
     */
    public function register(Request $request, ObjectManager $manager, UserPasswordEncoderInterface $encoder)
    {
        $user = new User();

        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid())
        {
            $hash = $encoder->encodePassword($user, $user->getHash());
            $user->setHash($hash);

            $manager->persist($user);
            $manager->flush();

            $this->addFlash(
                'success',
                'Votre inscription a été prise en compte'
            );

            return $this->redirectToRoute('account_login');
        }

        return $this->render('account/registration.html.twig',[
            'form' => $form->createView()
        ]);
    }

    /**
     * Permet de modifier le profil
     * @Route("/account/profile", name="account_profile")
     * @IsGranted("ROLE_USER")
     * @param Request $request
     * @param ObjectManager $manager
     * @return Response
     */
    public function profile(Request $request, ObjectManager $manager)
    {
        $user = $this->getUser();
        $form = $this->createForm(AccountType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $manager->persist($user);
            $manager->flush();

            $this->addFlash(
                'success',
                'Les données du profil ont été enregistrées'
            );
        }

        return $this->render('account/profile.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Permet de modifier le mot de passe
     * @Route("/account/password-update", name="account_password")
     * @IsGranted("ROLE_USER")
     * @param Request $request
     * @param ObjectManager $manager
     * @param UserPasswordEncoderInterface $encoder
     * @return Response
     */
    public function updatePassword(Request $request, ObjectManager $manager, UserPasswordEncoderInterface $encoder)
    {
        $passwordUpdate = new PasswordUpdate();
        $user = $this->getUser();
        $form = $this->createForm(PasswordUpdateType::class, $passwordUpdate);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            if(!password_verify($passwordUpdate->getOldPassword(),$user->getHash())){
                $form->get('oldPassword')->addError(new FormError('Le mot de passe actuel n\'est pas le bon'));
            } else {
                $newPassword = $passwordUpdate->getNewPassword();
                $user->setHash($encoder->encodePassword($user,$newPassword));

                $manager->persist($user);
                $manager->flush();

                $this->addFlash(
                    'success',
                    'Votre mot de passe a bien été modifiée'
                );

                return $this->redirectToRoute('homepage');
            }
        }

        return $this->render('account/password.html.twig',[
            'form' => $form->createView()
        ]);
    }

    /**
     * Permet d'afficher le profil de l'utilisateur connecté
     * @Route("/account", name="account_index")
     * @IsGranted("ROLE_USER")
     * @return Response
     */
    public function myAccount()
    {
        return $this->render('user/index.html.twig', [
            'user' => $this->getUser()
        ]);
    }

    /**
     * Permet d'afficher la liste des réservations de l'utilisateur
     * @Route("/account/bookings", name="account_bookings")
     * @return Response
     */
    public function bookings()
    {
        return $this->render('account/bookings.html.twig');
    }
}
