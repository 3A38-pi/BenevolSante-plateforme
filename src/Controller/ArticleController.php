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
use Symfony\Component\HttpFoundation\File\File;



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


    #[Route('/articleDescription', name: 'articleDescription')]
    public function goToArticleDescription(): Response
    {
        return $this->render('templates_users/articleDescription/articleDescription.html.twig', [
            'controller_name' => 'ArticleController',
        ]);
    }


    #[Route('/createArticle', name: 'createArticle')]
    public function createArticle(Request $request, #[Autowire('%image_dir%')]string $imageDir) 
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if($image = $form['image']->getData())
            {
                $filename = uniqid() . '.' . $image->guessExtension();
                $image->move(
                    $imageDir,
                    $filename
                );
                $article->setImage($filename);
                
            }

            $article->setImage($filename);

            $this->em->persist($article);
            $this->em->flush();

            // $this->addFlush('success', 'Article ajouté avec succès !');
            return $this->redirectToRoute('ArticleList');
           
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


}
