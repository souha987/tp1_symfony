<?php

namespace App\Controller;

use App\Entity\Tache;
use App\Repository\TacheRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TacheController extends AbstractController
{
    #[Route('/taches', name: 'app_taches')]
    public function index(TacheRepository $tacheRepository): Response
    {
        // Trie : les non terminées d'abord, puis par date de création récente
        $taches = $tacheRepository->findBy([], ['terminee' => 'ASC', 'dateCreation' => 'DESC']);
        
        return $this->render('taches/index.html.twig', [
            'taches' => $taches,
        ]);
    }

    #[Route('/taches/ajouter', name: 'app_tache_ajouter')]
    public function ajouter(EntityManagerInterface $em): Response
    {
        $tache = new Tache();
        $tache->setTitre('Apprendre Symfony');
        $tache->setDescription('Suivre le TP1 et comprendre les bases du framework');
        $tache->setTerminee(false);
        $tache->setDateCreation(new \DateTime());

        $em->persist($tache);
        $em->flush();

        $this->addFlash('success', 'Tâche créée avec succès !');
        return $this->redirectToRoute('app_taches');
    }

    #[Route('/taches/{id}', name: 'app_tache_detail', requirements: ['id' => '\d+'])]
    public function detail(Tache $tache): Response
    {
        return $this->render('taches/detail.html.twig', [
            'tache' => $tache,
        ]);
    }

    // BONUS : Route pour marquer une tâche comme terminée
    #[Route('/taches/{id}/terminer', name: 'app_tache_terminer', requirements: ['id' => '\d+'])]
    public function terminer(Tache $tache, EntityManagerInterface $em): Response
    {
        $tache->setTerminee(true);
        $em->flush();

        $this->addFlash('success', 'Tâche marquée comme terminée !');
        return $this->redirectToRoute('app_taches');
    }

    // BONUS : Route pour supprimer une tâche
    #[Route('/taches/{id}/supprimer', name: 'app_tache_supprimer', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function supprimer(Tache $tache, EntityManagerInterface $em): Response
    {
        $em->remove($tache);
        $em->flush();

        $this->addFlash('success', 'Tâche supprimée avec succès !');
        return $this->redirectToRoute('app_taches');
    }
}
