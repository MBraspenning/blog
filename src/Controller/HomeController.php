<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller; 
use App\Entity\Post;
use App\Form\SearchType;

class HomeController extends Controller
{
    /**
    * @Route("/", name="index")
    * @Method({"GET", "POST"})
    */
    public function index(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        
        $posts = $entityManager->getRepository(Post::class)->findLatest();
        $latestPosts = $entityManager->getRepository(Post::class)->findLatest();
        
        $search_form = $this->createForm(SearchType::class);
        
        $search_form->handleRequest($request);
        
        if ($search_form->isSubmitted() && $search_form->isValid())
        {
            $searchQuery = $search_form->getData()['Search'];
            
            return $this->redirectToRoute('blog_results', array(
                'query' => $searchQuery,
            ));
        }
        
        return $this->render('Home/index.html.twig', array(
            'posts' => $posts,
            'latestPosts' => $latestPosts,
            'search_form' => $search_form->createView(),
        ));
    }
}