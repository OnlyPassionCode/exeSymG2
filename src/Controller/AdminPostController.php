<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/post')]
final class AdminPostController extends AbstractController
{
    #[Route(name: 'app_admin_post_index', methods: ['GET'])]
    public function index(PostRepository $postRepository): Response
    {
        return $this->render('admin_post/index.html.twig', [
            'posts' => $postRepository->findAll(),
            'title' => "CRUD Posts",
        ]);
    }

    #[Route('/new', name: 'app_admin_post_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setPostDateCreated(new \DateTime());
            if($post->isPostPublished()) $post->setPostDatePublished($post->getPostDateCreated());
            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_post/new.html.twig', [
            'post' => $post,
            'form' => $form,
            'title' => "Nouveau Post",
        ]);
    }

    #[Route('/{id}', name: 'app_admin_post_show', methods: ['GET'])]
    public function show(Post $post): Response
    {
        return $this->render('admin_post/show.html.twig', [
            'post' => $post,
            'title' => 'Post '.$post->getPostTitle(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_post_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if($post->isPostPublished()){
                if($post->getPostDatePublished() === null) $post->setPostDatePublished(new \DateTime());
            }else $post->setPostDatePublished(null);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_post/edit.html.twig', [
            'post' => $post,
            'form' => $form,
            'title' => "Editer Post",
        ]);
    }

    #[Route('/{id}', name: 'app_admin_post_delete', methods: ['POST'])]
    public function delete(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($post);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_post_index', [], Response::HTTP_SEE_OTHER);
    }
}
