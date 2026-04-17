<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ArticlesController extends AbstractController
{
    #[Route('/articles', name: 'app_articles')]
    public function index(ArticleRepository $articleRepository): Response
    {
        return $this->render('articles/index.html.twig', [
            'articles' => $articleRepository->findAll(),
        ]);
    }

    #[Route('/articles/nouveau', name: 'app_article_nouveau')]
    #[IsGranted('ROLE_USER')]
    public function nouveau(Request $request, EntityManagerInterface $entityManager): Response
    {
        $article = new Article();

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $article->setAuteurUser($this->getUser());

            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirectToRoute('app_articles');
        }

        return $this->render('articles/nouveau.html.twig', [
            'formulaire' => $form->createView()
        ]);
    }

    #[Route('/articles/{id}', name: 'app_article_detail')]
    public function detail(Article $article): Response
    {
        return $this->render('articles/detail.html.twig', [
            'article' => $article,
        ]);
    }

    #[Route('/articles/{id}/modifier', name: 'app_article_modifier')]
    #[IsGranted('ROLE_USER')]
    public function modifier(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser() !== $article->getAuteurUser()) {
            throw $this->createAccessDeniedException("Vous n'êtes pas l'auteur !");
        }

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_articles');
        }

        return $this->render('articles/modifier.html.twig', [
            'formulaire' => $form->createView(),
            'article' => $article
        ]);
    }

    #[Route('/articles/{id}/supprimer', name: 'app_article_supprimer', methods: ['POST'])]
    public function supprimer(Article $article, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('supprimer_' . $article->getId(), $request->request->get('_token'))) {
            $em->remove($article);
            $em->flush();
        }

        return $this->redirectToRoute('app_articles');
    }
}