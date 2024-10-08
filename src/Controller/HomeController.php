<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\Section;
use App\Form\CommentType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
    public function section(Section $section, AuthenticationUtils $authenticationUtils): Response
    {
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        return $this->render('home/section.html.twig', [
            'title' => 'Section '.$section->getSectionTitle(),
            'section' => $section,
            'last_username' => $lastUsername,
            'active' => 'Section',
            'posts' => $section->getPosts(),
        ]);
    }

    #[Route('/post/{id}', name: 'post')]
    public function post(Request $request, Post $post, AuthenticationUtils $authenticationUtils, EntityManagerInterface $entityManager): Response
    {
        if(!$post->isPostPublished()){
            throw $this->createNotFoundException();
        }
        $user = $this->getUser();
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($user !== null && $form->isSubmitted() && $form->isValid()) {
            $comment->setCommentDateCreated(new \DateTime())
                    ->setCommentPublished(true)
                    ->setPost($post)
                    ->setUser($user);
            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute('post', ['id' => $post->getId()], Response::HTTP_SEE_OTHER);
        }

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        return $this->render('home/post.html.twig', [
            'title' => 'Post '.$post->getPostTitle(),
            'post' => $post,
            'last_username' => $lastUsername,
            'form' => $form,
            'comments' => $post->getComments()
        ]);
    }
}
