<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SessionController extends AbstractController
{
    #[Route('/session', name: 'app_session')]
    public function index(RequestStack $requestStack): Response
    {
        $session = $requestStack->getSession();

        // récupérer valeur
        $visites = $session->get('visites', 0);

        // incrémenter
        $visites++;

        // stocker
        $session->set('visites', $visites);

        return new Response("Nombre de visites : " . $visites);
    }
}