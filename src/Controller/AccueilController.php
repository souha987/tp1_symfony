<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Service\TextFormatter;
use App\Service\ReadingTimeCalculator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AccueilController extends AbstractController
{
    #[Route('/accueil', name: 'app_accueil')]
    #[Route('/', name: 'app_home')]
    public function index(
        ArticleRepository $articleRepository,
        TextFormatter $textFormatter,
        ReadingTimeCalculator $calculator
    ): Response {
        // 🆕 6 derniers articles PUBLIES
        $recentArticles = $articleRepository->findBy([
            'publie' => true
        ], [
            'dateCreation' => 'DESC'
        ], 6);

        // 🆕 Statistiques
        $totalArticles = $articleRepository->count([]);
        $publishedArticles = $articleRepository->count(['publie' => true]);

        return $this->render('accueil/index.html.twig', [
            'articles' => $recentArticles,
            'stats' => [
                'total' => $totalArticles,
                'published' => $publishedArticles,
                'drafts' => $totalArticles - $publishedArticles
            ],
            // 🆕 Demo services
            'demo_text' => $textFormatter->filter('Cet article est nul et spam !'),
            'demo_time' => $calculator->calculate('Texte de 400 mots pour test du service de lecture.'),
        ]);
    }
}