<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller; 
use App\Entity\Post;

class HomeController extends Controller
{
    /**
    * @Route("/", name="index")
    * @Method("GET")
    */
    public function index()
    {
        $entityManager = $this->getDoctrine()->getManager();
        
        $posts = $entityManager->getRepository(Post::class)->findLatest();
        $latestPosts = $entityManager->getRepository(Post::class)->findLatest();
        
        return $this->render('Home/index.html.twig', array(
            'posts' => $posts,
            'latestPosts' => $latestPosts,
        ));
    }
}