<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class RedacController extends AbstractController
{
    #[Route('/redac', name: 'app_redac')]
    public function index(AuthenticationUtils $authenticationUtils, PostRepository $postRepository): Response
    {
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        /**
         * @var User
         */
        $user = $this->getUser();
        return $this->render('redac/index.html.twig', [
            'last_username' => $lastUsername,
            'active' => 'Accueil',
            'posts' => $postRepository->findAllPostsByUser($user->getId()),
            'title' => 'Accueil'
        ]);
    }
}
