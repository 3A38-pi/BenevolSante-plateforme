<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\ArticleType;
use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use App\Entity\Commentaire;
use App\Form\CommentaireType;
use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;

final class ArticleController extends AbstractController
{
    private $em; 

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/article', name: 'ArticleList')]
    public function goToArticleList(): Response
    {
        $articles = $this->em->getRepository(Article::class)->findAll();

        return $this->render('templates_users/article/articleList.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/articleDescription/{id}', name: 'articleDescription')]
    public function goToArticleDescription(Request $request, $id): Response
    {   
        $article = $this->em->getRepository(Article::class)->find($id);

        if (!$article) {
            throw $this->createNotFoundException("L'article n'existe pas.");
        }

        // Création d'un nouveau commentaire
        $commentaire = new Commentaire();
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $commentaire->setArticle($article);
            // Exemple d'user en dur :
            $commentaire->setUser($this->getUser());

            $this->em->persist($commentaire);
            $this->em->flush();

            return $this->redirectToRoute('articleDescription', ['id' => $article->getId()]);
        }

        return $this->render('templates_users/articleDescription/articleDescription.html.twig', [
            'article' => $article,
            'commentaires' => $article->getCommentaires(),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/createArticle', name: 'createArticle')]
    public function createArticle(Request $request, #[Autowire('%image_dir%')] string $imageDir) 
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($image = $form['image']->getData()) {
                $filename = uniqid() . '.' . $image->guessExtension();
                $image->move($imageDir, $filename);
                $article->setImage($filename);
            }

            $this->em->persist($article);
            $this->em->flush();

            return $this->redirectToRoute('adminArticleList');
        }
       
        return $this->render('templates_admin/articleForm/articleForm.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/adminArticleList', name: 'adminArticleList')]
    public function goToAdminArticleList(): Response
    {
        $articles = $this->em->getRepository(Article::class)->findAll();

        return $this->render('templates_admin/articleAdminList/articleAdminList.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/editArticle/{id}', name: 'editArticle')]
    public function editArticle(Request $request, $id )
    {
        $article = $this->em->getRepository(Article::class)->find($id);
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        return $this->render('articleForm/articleForm.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Nouvelle route pour modifier un article en AJAX (POST JSON)
     */
    #[Route('/article/modifier/{id}', name: 'article_modifier', methods: ['POST'])]
public function modifierArticle(Request $request, $id): JsonResponse
{
    $article = $this->em->getRepository(Article::class)->find($id);

    if (!$article) {
        return new JsonResponse(["success" => false, "message" => "Article introuvable"], 404);
    }

    $data = json_decode($request->getContent(), true);

    if (!isset($data['titre'], $data['categorie'], $data['description'])) {
        return new JsonResponse(["success" => false, "message" => "Données invalides"], 400);
    }

    $article->setTitre($data['titre']);
    // Remplacer l'appel à setTags par setCategorie :
    $article->setCategorie($data['categorie']);
    $article->setDescription($data['description']);

    $this->em->persist($article);
    $this->em->flush();

    return new JsonResponse(["success" => true, "message" => "Article modifié avec succès"]);
}


    #[Route('/commentaire/modifier/{id}', name: 'commentaire_modifier', methods: ['POST'])]
    public function modifierCommentaire(Request $request, Commentaire $commentaire, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['content']) && !empty($data['content'])) {
            $commentaire->setContent($data['content']);
            $entityManager->persist($commentaire);
            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Commentaire modifié avec succès',
                'newContent' => $commentaire->getContent()
            ]);
        }

        return $this->json(['success' => false, 'message' => 'Données invalides'], 400);
    }

    #[Route('/commentaire/supprimer/{id}', name: 'supprimer_commentaire', methods: ['DELETE'])]
    public function supprimerCommentaire($id, EntityManagerInterface $em): JsonResponse
    {
        $commentaire = $em->getRepository(Commentaire::class)->find($id);

        if (!$commentaire) {
            return new JsonResponse(["success" => false, "message" => "Commentaire introuvable"], 404);
        }

        $em->remove($commentaire);
        $em->flush();

        return new JsonResponse(["success" => true]);
    }

    #[Route('/article/supprimer/{id}', name: 'supprimer_article', methods: ['DELETE'])]
    public function supprimerArticle($id, EntityManagerInterface $em): JsonResponse
    {
        $article = $em->getRepository(Article::class)->find($id);

        if (!$article) {
            return new JsonResponse(["success" => false, "message" => "Article introuvable"], 404);
        }

        $em->remove($article);
        $em->flush();

        return new JsonResponse(["success" => true]);
    }

    #[Route('/article/{id}/commentaires', name: 'get_article_commentaires', methods: ['GET'])]
    public function getArticleCommentaires($id): JsonResponse
    {
        $article = $this->em->getRepository(Article::class)->find($id);

        if (!$article) {
            return new JsonResponse(["success" => false, "message" => "Article introuvable"], 404);
        }

        $commentaires = $article->getCommentaires()->map(function ($commentaire) {
            return [
                "content" => $commentaire->getContent(),
            ];
        });

        return new JsonResponse(["success" => true, "commentaires" => $commentaires]);
    }

    
}
