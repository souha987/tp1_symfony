<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RequestStack;

class AccueilController extends AbstractController
{
  #[Route('/accueil', name: 'app_accueil')]
public function index(RequestStack $requestStack): Response
{
    $session = $requestStack->getSession();

    // compteur visites
    $visites = $session->get('visites', 0);
    $visites++;
    $session->set('visites', $visites);

    return $this->render('accueil/index.html.twig', [
        'visites' => $visites
    ]);
}

    #[Route('/', name: 'app_home')]
    public function home(): Response
    {
        return $this->redirectToRoute('app_accueil');
    }
}
