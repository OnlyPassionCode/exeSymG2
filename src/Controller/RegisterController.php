<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserActivationToken;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class RegisterController extends AbstractController
{

    public function __construct(
        private MailerInterface $mailer,
        private UserPasswordHasherInterface $hasher,
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, AuthenticationUtils $authenticationUtils, Security $security): Response
    {
        if($this->getUser() !== null)
            return $this->redirectToRoute('app_home');
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($this->hasher->hashPassword($user, $user->getPassword()));
            $user->setUniqid(uniqid(more_entropy: true));
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            $security->login($user);
            $this->sendMailValidation($user);
            return $this->render('home/register.html.twig', [
                'title' => 'Compte crée',
                'last_username' => $lastUsername,
                'active' => 'Registered',
                'registered' => true
            ]);
        }
        return $this->render('home/register.html.twig', [
            'title' => 'S\'enregistrer',
            'last_username' => $lastUsername,
            'active' => 'Register',
            'registerForm' => $form
        ]);
    }

    private function sendMailValidation(User $user): void{
        // Si aucun DSN dans les .env
        if(!isset($_ENV['MAILER_DSN'])){
            $user->setActivate(true);
            return;
        }
        TokenController::sendEmailValidation($user, $this->entityManager, $this->mailer);
    }
}
