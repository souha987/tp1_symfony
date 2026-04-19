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
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
class ArticlesController extends AbstractController
{
    #[Route('/articles', name: 'app_articles')]
    #[Route('/articles', name: 'app_articles')]
public function index(ArticleRepository $articleRepository, RequestStack $requestStack): Response
{
    $session = $requestStack->getSession();

    // compteur visites
    $visites = $session->get('visites', 0);
    $visites++;
    $session->set('visites', $visites);

    return $this->render('articles/index.html.twig', [
        'articles' => $articleRepository->findAll(),
        'visites' => $visites
    ]);
}
      
    #[Route('/articles/nouveau', name: 'app_article_nouveau')]
    #[IsGranted('ROLE_USER')]
    public function nouveau(Request $request, EntityManagerInterface $em, MailerInterface $mailer): Response
    {
        $article = new Article();

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
             $email = (new Email())
    ->from('tp@symfony.com')
    ->to('test@gmail.com')
    ->subject('Nouvel article créé')
    ->text('Un article vient d’être ajouté sur le site.');

$mailer->send($email);
            // lien utilisateur connecté
            $article->setAuteurUser($this->getUser());

            // date automatique
            if (!$article->getDateCreation()) {
                if (!$article->getDateCreation()) {
                $article->setDateCreation(new \DateTimeImmutable());
}
            }

            // publié par défaut
            $article->setPublie(true);

            $em->persist($article);
            $em->flush();

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
    public function modifier(Request $request, Article $article, EntityManagerInterface $em): Response
    {
        if ($this->getUser() !== $article->getAuteurUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
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
        if ($this->isCsrfTokenValid('supprimer_'.$article->getId(), $request->request->get('_token'))) {
            $em->remove($article);
            $em->flush();
        }

        return $this->redirectToRoute('app_articles');
    }
    #[Route('/articles/publies', name: 'app_articles_publies')]
public function articlesPublies(ArticleRepository $articleRepository): Response
{
    $articles = $articleRepository->findPublishedArticles();

    return $this->render('articles/index.html.twig', [
        'articles' => $articles
    ]);
}
}