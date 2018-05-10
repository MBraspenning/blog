<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller; 
use App\Entity\Post;
use App\Form\SearchType;
use App\Utils\Search\SearchUtil;

class HomeController extends Controller
{
    private $SearchUtil;
    
    public function __construct(SearchUtil $SearchUtil)
    {
        $this->SearchUtil = $SearchUtil;
    }
    
    /**
    * @Route("/", name="index")
    * @Method({"GET", "POST"})
    */
    public function index(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        
        $posts = $entityManager->getRepository(Post::class)->findLatest();
        $latestPosts = $entityManager->getRepository(Post::class)->findLatest();        
        
        $search_form = $this->SearchUtil->createSearchForm();
        if ($search_query = $this->SearchUtil->handleSearchForm($request, $search_form))
        {
            return $this->redirectToRoute('blog_results', array(
                'query' => $search_query,
            ));    
        }
        
        return $this->render('Home/index.html.twig', array(
            'posts' => $posts,
            'latestPosts' => $latestPosts,
            'search_form' => $search_form->createView(),
        ));
    }
}