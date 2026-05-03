<?php

namespace App\Controller\Api;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use App\Repository\CategorieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/api/v1/articles', name: 'api_articles_')]
class ArticleController extends AbstractController
{
    // =========================================================
    // GET /api/v1/articles
    // Retourne la liste de tous les articles
    // =========================================================
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(
        ArticleRepository $repository,
        SerializerInterface $serializer
    ): JsonResponse {
        $articles = $repository->findAll();

        $json = $serializer->serialize(
            $articles,
            'json',
            ['groups' => 'article:read']
        );

        // true = le contenu est déjà du JSON sérialisé, ne pas ré-encoder
        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    // =========================================================
    // GET /api/v1/articles/{id}
    // Retourne un article par son ID
    // Symfony injecte automatiquement l'objet Article via ParamConverter
    // Retourne 404 automatiquement si l'ID n'existe pas
    // =========================================================
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(
        Article $article,
        SerializerInterface $serializer
    ): JsonResponse {
        $json = $serializer->serialize(
            $article,
            'json',
            ['groups' => 'article:read']
        );

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    // =========================================================
    // POST /api/v1/articles
    // Crée un nouvel article depuis le body JSON
    // Retourne 201 Created en succès
    // Retourne 422 Unprocessable Entity si validation échoue
    // Retourne 400 Bad Request si la catégorie est introuvable
    // =========================================================
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        CategorieRepository $categorieRepository
    ): JsonResponse {
        // 1. Désérialiser le JSON reçu en objet Article
        $article = $serializer->deserialize(
            $request->getContent(),
            Article::class,
            'json'
        );

        // 2. Date de création = maintenant (générée automatiquement)
        $article->setDateCreation(new \DateTime());

        // 3. Si publie n'est pas fourni dans le JSON → false par défaut
        if ($article->isPublie() === null) {
            $article->setPublie(false);
        }

        // 4. Gestion de la catégorie via categorie_id
        //    On relit le JSON brut car le serializer ne gère pas les relations par ID
        $data = json_decode($request->getContent(), true);

        if (isset($data['categorie_id'])) {
            $categorie = $categorieRepository->find($data['categorie_id']);
            if (!$categorie) {
                return $this->json([
                    'error' => 'Catégorie non trouvée avec l\'id ' . $data['categorie_id'],
                ], Response::HTTP_BAD_REQUEST);
            }
            $article->setCategorie($categorie);
        }

        // 5. Validation des contraintes (@Assert) définies sur l'entité
        $errors = $validator->validate($article);
        if (count($errors) > 0) {
            $errorsArray = [];
            foreach ($errors as $error) {
                $errorsArray[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(
                ['errors' => $errorsArray],
                Response::HTTP_UNPROCESSABLE_ENTITY  // 422
            );
        }

        // 6. Persistance en base de données
        $em->persist($article);
        $em->flush();

        return $this->json(
            $article,
            Response::HTTP_CREATED,  // 201
            [],
            ['groups' => 'article:read']
        );
    }

    // =========================================================
    // PUT /api/v1/articles/{id}
    // Met à jour uniquement les champs fournis dans le body JSON
    // Retourne 200 OK en succès
    // Retourne 400 Bad Request si JSON invalide ou catégorie introuvable
    // Retourne 422 Unprocessable Entity si validation échoue
    // =========================================================
    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(
        Article $article,
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        CategorieRepository $categorieRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            return $this->json(
                ['error' => 'JSON invalide.'],
                Response::HTTP_BAD_REQUEST
            );
        }

        // Mise à jour partielle : on ne modifie que les champs présents dans le JSON
        if (isset($data['titre'])) {
            $article->setTitre($data['titre']);
        }

        if (isset($data['contenu'])) {
            $article->setContenu($data['contenu']);
        }

        if (isset($data['auteur'])) {
            $article->setAuteur($data['auteur']);
        }

        // array_key_exists est obligatoire ici car isset(false) = false
        // On raterait la mise à jour si publie vaut false
        if (array_key_exists('publie', $data)) {
            $article->setPublie((bool) $data['publie']);
        }

        // Gestion de la catégorie :
        //   - clé absente du JSON → on ne touche pas à la catégorie existante
        //   - categorie_id: null     → on dissocie la catégorie
        //   - categorie_id: 5        → on associe la catégorie avec l'id 5
        if (array_key_exists('categorie_id', $data)) {
            if ($data['categorie_id'] === null) {
                $article->setCategorie(null);
            } else {
                $categorie = $categorieRepository->find($data['categorie_id']);
                if (!$categorie) {
                    return $this->json([
                        'error' => 'Catégorie non trouvée avec l\'id ' . $data['categorie_id'],
                    ], Response::HTTP_BAD_REQUEST);
                }
                $article->setCategorie($categorie);
            }
        }

        // Validation des contraintes
        $errors = $validator->validate($article);
        if (count($errors) > 0) {
            $errorsArray = [];
            foreach ($errors as $error) {
                $errorsArray[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(
                ['errors' => $errorsArray],
                Response::HTTP_UNPROCESSABLE_ENTITY  // 422
            );
        }

        // Pas de persist() nécessaire : l'entité est déjà managée par Doctrine
        $em->flush();

        return $this->json(
            $article,
            Response::HTTP_OK,  // 200
            [],
            ['groups' => 'article:read']
        );
    }

    // =========================================================
    // DELETE /api/v1/articles/{id}
    // Supprime un article
    // Retourne 204 No Content (pas de body dans la réponse)
    // =========================================================
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(
        Article $article,
        EntityManagerInterface $em
    ): JsonResponse {
        $em->remove($article);
        $em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);  // 204
    }
}