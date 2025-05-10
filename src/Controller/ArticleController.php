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
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Notification;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
use App\Service\OpenAIService;
use App\Service\CommentAnalyzer;
use App\Service\NotificationMailer;

final class ArticleController extends AbstractController
{
    private $em;
    private $openAIService;
    private $commentAnalyzer;

    public function __construct(EntityManagerInterface $em, OpenAIService $openAIService, CommentAnalyzer $commentAnalyzer)
    {
        $this->em = $em;
        $this->openAIService = $openAIService;
        $this->commentAnalyzer = $commentAnalyzer;
    }

    #[Route('/article/{page}', name: 'ArticleList', requirements: ['page' => '\d+'], defaults: ['page' => 1])]
    public function goToArticleList(int $page = 1): Response
    {
        $articlesPerPage = 9;
        $offset = ($page - 1) * $articlesPerPage;

        $articles = $this->em->getRepository(Article::class)
                             ->findBy([], ['id' => 'DESC'], $articlesPerPage, $offset);

        $totalArticles = $this->em->getRepository(Article::class)->count([]);
        $totalPages = (int) ceil($totalArticles / $articlesPerPage);

        return $this->render('templates_users/article/articleList.html.twig', [
            'articles' => $articles,
            'currentPage' => $page,
            'totalPages'  => $totalPages,
        ]);
    }

    #[Route('/articleDescription/{id}', name: 'articleDescription')]
    public function goToArticleDescription(Request $request, int $id, CommentAnalyzer $commentAnalyzer): Response
    {
        $article = $this->em->getRepository(Article::class)->find($id);
        if (!$article) {
            throw $this->createNotFoundException("L'article n'existe pas.");
        }

        // Création du formulaire pour un nouveau commentaire
        $commentaire = new Commentaire();
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $commentContent = $commentaire->getContent();

            // Appel à l'API pour analyser le commentaire
            $analysis = $commentAnalyzer->analyzeComment($commentContent);

            // Vérifie dans la catégorie "profanity" si un match avec intensité "high" ou "medium" existe
            $rejectComment = false;
            if (isset($analysis['profanity']['matches']) && is_array($analysis['profanity']['matches'])) {
                foreach ($analysis['profanity']['matches'] as $match) {
                    if ((isset($match['intensity']) && $match['intensity'] === 'high') || (isset($match['intensity']) && $match['intensity'] === 'medium')) {
                        $rejectComment = true;
                        break;
                    }
                }
            }

            if ($rejectComment) {
                // Affichage d'un message d'erreur et redirection sans sauvegarde
                $this->addFlash('danger', 'Votre commentaire contient des propos trop intenses et n\'a pas été publié.');
                return $this->redirectToRoute('articleDescription', ['id' => $article->getId()]);
            }

            // Le commentaire est accepté : on le sauvegarde en base de données
            $commentaire->setEtat("valide");
            $commentaire->setArticle($article);
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

    // #[Route('/createArticle', name: 'createArticle')]
    // public function createArticle(Request $request, #[Autowire('%image_dir%')] string $imageDir)
    // {
    //     $article = new Article();
    //     $form = $this->createForm(ArticleType::class, $article);
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         if ($image = $form['image']->getData()) {
    //             $filename = uniqid() . '.' . $image->guessExtension();
    //             $image->move($imageDir, $filename);
    //             $article->setImage($filename);
    //         }

    //         $this->em->persist($article);
    //         $this->em->flush();

    //         return $this->redirectToRoute('adminArticleList');
    //     }
       
    //     return $this->render('templates_admin/articleForm/articleForm.html.twig', [
    //         'form' => $form->createView(),
    //     ]);
    // }

    #[Route('/createArticle', name: 'createArticle')]
public function createArticle(Request $request, #[Autowire('%image_dir%')] string $imageDir): Response
{
    $article = new Article();
    $form = $this->createForm(ArticleType::class, $article);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        if ($image = $form['image']->getData()) {
            $filename = uniqid() . '.' . $image->guessExtension();
            $image->move($imageDir, $filename);
            $article->setImage($filename); // => base contient juste le nom
        }

        $this->em->persist($article);
        $this->em->flush();

        return $this->redirectToRoute('adminArticleList');
    }

    return $this->render('templates_admin/articleForm/articleForm.html.twig', [
        'form' => $form->createView(),
    ]);
}

#[Route('/api/articles', name: 'api_articles', methods: ['GET'])]
public function apiArticles(Request $request): JsonResponse
{
    $articles = $this->em->getRepository(Article::class)->findAll();
    $host = $request->getSchemeAndHttpHost();

    $data = [];
    foreach ($articles as $article) {
        $data[] = [
            'id' => $article->getId(),
            'titre' => $article->getTitre(),
            'description' => $article->getDescription(),
            'categorie' => $article->getCategorie(),
            'image' => $article->getImage(),
            'image_url' => $host . '/uploads/images/' . $article->getImage(), // 🔥 pour JavaFX
            'likes' => $article->getLikes(),
            'dislikes' => $article->getDislikes(),
            'created_at' => $article->getCreatedAt()?->format('Y-m-d H:i:s'),
        ];
    }

    return new JsonResponse($data);
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
    public function editArticle(Request $request, $id)
    {
        $article = $this->em->getRepository(Article::class)->find($id);
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);
        return $this->render('articleForm/articleForm.html.twig', [
            'form' => $form->createView(),
        ]);
    }

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

    // Suppression d'un article via soumission de formulaire (sans AJAX)
    #[Route('/article/supprimer/{id}', name: 'supprimer_article', methods: ['POST'])]
    public function supprimerArticle($id, EntityManagerInterface $em): Response
    {
        $article = $em->getRepository(Article::class)->find($id);
        if (!$article) {
            $this->addFlash('danger', "Article introuvable");
            return $this->redirectToRoute('adminArticleList');
        }
        $em->remove($article);
        $em->flush();
        $this->addFlash('success', "Article supprimé avec succès");
        return $this->redirectToRoute('adminArticleList');
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
                "id"      => $commentaire->getId(),
                "nom"     => $commentaire->getUser() ? $commentaire->getUser()->getNom() : "",
                "prenom"  => $commentaire->getUser() ? $commentaire->getUser()->getPrenom() : "",
                "content" => $commentaire->getContent(),
                "etat"    => $commentaire->getEtat(),
            ];
        })->toArray();
        return new JsonResponse(["success" => true, "commentaires" => $commentaires]);
    }

    #[Route('/commentaire/desactiver/{id}', name: 'desactiver_commentaire', methods: ['POST'])]
    public function desactiverCommentaire(Commentaire $commentaire, EntityManagerInterface $em, NotificationMailer $notificationMailer): JsonResponse
    {
        $commentaire->setEtat("non valide");
        $em->persist($commentaire);


    $commentaire->setEtat("non valide");
    $em->persist($commentaire);

    $notification = new Notification();
    $notification->setMessage($commentaire->getContent());
    $notification->setUser($commentaire->getUser());
    $notification->setCommentaire($commentaire);
    $notification->setType(Notification::TYPE_COMMENTAIRE); // Définir le type de notification
    $em->persist($notification);

    $em->flush();
    $transport = Transport::fromDsn('smtp://amroush123@gmail.com:npcfowmbtolgyqfe@smtp.gmail.com:587');
    $mailer = new Mailer($transport);
    $recipientEmail = $commentaire->getUser()->getEmail();
    $email = (new Email())
        ->from('amroush123@gmail.com')
        ->to($recipientEmail)
        ->subject('Notification : Commentaire désactivé')
        ->text(sprintf(
            "Bonjour %s,\n\nVotre commentaire a été désactivé.\nContenu: %s\n\nCordialement,\nL’équipe.",
            $commentaire->getUser()->getNom(),
            $commentaire->getContent()
        ));
    $mailer->send($email);
    
    return new JsonResponse(["success" => true, "message" => "Commentaire désactivé"]);
}



    #[Route('/execute-script', name: 'execute_script')]
    public function executeScript(Request $request): Response
    {
        $text = $request->query->get('text', 'Good');
        ob_start();
        include $this->getParameter('kernel.project_dir').'/templates/script.php';
        $result = ob_get_clean();
        return new Response($result);
    }
}
