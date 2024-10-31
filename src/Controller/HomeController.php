<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\Section;
use App\Entity\User;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class HomeController extends AbstractController
{

    public function __construct(
        private AuthenticationUtils $authenticationUtils,
        private Security $security
    ) {
    }
    
    #[Route('/', name: 'app_home')]
    public function index(PostRepository $postRepository): Response
    {
        // last username entered by the user
        $lastUsername = $this->authenticationUtils->getLastUsername();

        return $this->render('home/index.html.twig', [
            'last_username' => $lastUsername,
            'active' => 'Accueil',
            'posts' => $postRepository->findTenLastPublishedPosts(),
            'title' => 'Accueil'
        ]);
    }
    
    #[Route('/section/{sectionSlug}', name: 'section')]
    public function section(Section $section, PostRepository $postRepository): Response
    {
        // last username entered by the user
        $lastUsername = $this->authenticationUtils->getLastUsername();
        return $this->render('home/section.html.twig', [
            'title' => 'Section '.$section->getSectionTitle(),
            'section' => $section,
            'last_username' => $lastUsername,
            'active' => 'Section',
            'posts' => $postRepository->findPublishedPostsBySection($section->getId()),
        ]);
    }

    #[Route('/user/{id}', name: 'user')]
    public function user(User $user, PostRepository $postRepository): Response
    {
        // last username entered by the user
        $lastUsername = $this->authenticationUtils->getLastUsername();
        return $this->render('home/user.html.twig', [
            'title' => 'Utilisateur '.$user->getFullname() ?? $user->getUsername(),
            'user' => $user,
            'last_username' => $lastUsername,
            'active' => 'Section',
            'posts' => $postRepository->findPublishedPostsByUser($user->getId()),
        ]);
    }

    #[Route('/post/{postSlug}', name: 'post')]
    public function post(Request $request, Post $post, CommentRepository $commentRepository, EntityManagerInterface $entityManager): Response
    {
        if(!$post->isPostPublished()){
            throw $this->createNotFoundException();
        }
        /**
         * @var User
         */
        $user = $this->getUser();
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        
        if ($user !== null && $user->isActivate() && $form->isSubmitted() && $form->isValid()) {
            $comment->setCommentDateCreated(new \DateTime())
            ->setCommentPublished(false)
            ->setPost($post)
            ->setUser($user);

            if($this->security->isGranted('ROLE_ADMIN'))
                $comment->setCommentPublished(true);

            $entityManager->persist($comment);
            $entityManager->flush();
            
            return $this->redirectToRoute('post', ['postSlug' => $post->getPostSlug()], Response::HTTP_SEE_OTHER);
        }

        // last username entered by the user
        $lastUsername = $this->authenticationUtils->getLastUsername();
        return $this->render('home/post.html.twig', [
            'title' => 'Post '.$post->getPostTitle(),
            'post' => $post,
            'last_username' => $lastUsername,
            'commentForm' => $form,
            'comments' => $commentRepository->findPublishedCommentsByPost($post->getId())
        ]);
    }
}
