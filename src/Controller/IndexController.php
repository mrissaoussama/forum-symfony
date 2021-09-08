<?php

namespace App\Controller;
use App\Repository\CategorieRepository;
use App\Repository\PostRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    /**
     * @Route("/index", name="index")
     */
    public function index(CategorieRepository $categorieRepository,PostRepository $postRepository): Response
    {$mostviewedposts=($postRepository->findMostViewedPosts());
    $recentlyactiveposts=($postRepository->findRecentlyActivePosts());
        foreach($mostviewedposts as $post)
        {if (strlen($post->getContent())>50)
            {
            $result = substr($post->getContent(), 0, 20)."...";
            $post->setContent($result);
            }
        }
        foreach($recentlyactiveposts as $post)
        {if (strlen($post->getContent())>50)
            {
            $result = substr($post->getContent(), 0, 20)."...";
            $post->setContent($result);
            }
        }
        return $this->render('index/index.html.twig', [
            'categories' => $categorieRepository->findAll(),
            'categorieposts' => $categorieRepository->findAllPostCount(),
'categorielastpost' =>($categorieRepository->findAllLastPost()),
'mostviewedposts'=>$mostviewedposts,
'recentlyactiveposts'=>$recentlyactiveposts,
        ]);
    }
    
}
