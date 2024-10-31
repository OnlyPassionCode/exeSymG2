<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/redac/post')]
final class RedacPostController extends AbstractController
{
    #[Route('/new', name: 'app_redac_post_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setPostPublished(false);
            $post->setPostDateCreated(new \DateTime());
            $post->setUser($this->getUser());
            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute('app_redac', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('redac_post/new.html.twig', [
            'post' => $post,
            'form' => $form,
            'title' => "Nouveau Post",
        ]);
    }

    #[Route('/{id}/edit', name: 'app_redac_post_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_redac', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('redac_post/edit.html.twig', [
            'post' => $post,
            'form' => $form,
            'title' => "Editer Post",
        ]);
    }

    #[Route('/{id}', name: 'app_redac_post_hide', methods: ['POST'])]
    public function hide(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('hide'.$post->getId(), $request->getPayload()->getString('_token'))) {
            $post->setPostPublished(false);
            $post->setPostDatePublished(null);
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_redac', [], Response::HTTP_SEE_OTHER);
    }
}
