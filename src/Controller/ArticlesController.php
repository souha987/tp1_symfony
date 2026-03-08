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
        $articles = $articleRepository->findAll();

        return $this->render('articles/index.html.twig', [
            'articles' => $articles,
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
        // Associer l'utilisateur connecté comme auteur
        $article->setAuteurUser($this->getUser());
        
        $entityManager->persist($article);
        $entityManager->flush();

        $this->addFlash('success', 'Article créé avec succès !');
        return $this->redirectToRoute('app_articles');
    }

    return $this->render('articles/nouveau.html.twig', [
        'form' => $form->createView(),
    ]);
}
    #[Route('/articles/{id}', name: 'app_article_detail', requirements: ['id' => '\d+'])]
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
    // Vérifier que l'utilisateur actuel est l'auteur de l'article
    if ($this->getUser() !== $article->getAuteurUser()) {
        throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier cet article.');
    }

    $form = $this->createForm(ArticleType::class, $article);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();
        
        $this->addFlash('success', 'Article modifié avec succès !');
        return $this->redirectToRoute('app_articles');
    }

    return $this->render('articles/modifier.html.twig', [
        'form' => $form->createView(),
        'article' => $article,
    ]);
}

    #[Route('/articles/{id}/supprimer', name: 'app_article_supprimer', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function supprimer(Article $article, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('supprimer_' . $article->getId(), $request->request->get('_token'))) {
            $em->remove($article);
            $em->flush();
            $this->addFlash('success', 'Article supprimé avec succès.');
        } else {
            $this->addFlash('danger', 'Token CSRF invalide. Suppression annulée.');
        }

        return $this->redirectToRoute('app_articles');
    }
}
