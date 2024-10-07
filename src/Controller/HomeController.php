<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Section;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(AuthenticationUtils $authenticationUtils): Response
    {
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('home/index.html.twig', [
            'last_username' => $lastUsername,
            'active' => 'Accueil',
            'title' => 'Accueil'
        ]);
    }
    
    #[Route('/section/{id}', name: 'section')]
    public function section(Section $section, PostRepository $postRepository, AuthenticationUtils $authenticationUtils): Response
    {
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        return $this->render('home/section.html.twig', [
            'title' => 'Section '.$section->getSectionTitle(),
            'section' => $section,
            'last_username' => $lastUsername,
            'active' => 'Section',
            'posts' => $postRepository->findAll()
        ]);
    }

    #[Route('/post/{id}', name: 'post')]
    public function post(Post $post, AuthenticationUtils $authenticationUtils): Response
    {
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        return $this->render('home/post.html.twig', [
            'title' => 'Section '.$post->getPostTitle(),
            'post' => $post,
            'last_username' => $lastUsername,
        ]);
    }
}
