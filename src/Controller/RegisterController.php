<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class RegisterController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, AuthenticationUtils $authenticationUtils, UserPasswordHasherInterface $hasher, EntityManagerInterface $entityManager, Security $security): Response
    {
        if($this->getUser() !== null)
            return $this->redirectToRoute('app_home');
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($hasher->hashPassword($user, $user->getPassword()));
            $user->setUniqid(uniqid(more_entropy: true));
            $entityManager->persist($user);
            $entityManager->flush();
            $security->login($user);
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
}
