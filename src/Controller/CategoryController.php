<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use App\Entity\Category;
use App\Entity\Post;
use App\Utils\Search\SearchUtil;
use App\Utils\Pagination\PaginationUtil;
use App\Form\CategoryType;

class CategoryController extends Controller
{
    private $SearchUtil;
    private $PaginationUtil;
    
    public function __construct(SearchUtil $SearchUtil, PaginationUtil $PaginationUtil)
    {
        $this->SearchUtil = $SearchUtil;
        $this->PaginationUtil = $PaginationUtil;
    }
    
    /**
    * @Route("blog/category/new", name="category_create")
    * @Security("has_role('ROLE_ADMIN')")
    * @Method({"GET", "POST"})
    */
    public function create(Request $request)
    {
        $category = new Category();
        
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid())
        {
            $category = $form->getData();
            
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($category);
            $entityManager->flush();
            
            return $this->redirectToRoute('blog_index');
        }
        
        return $this->render('Category/new.html.twig', array(
            'form' => $form->createView(),
        ));
    }
    
    /**
    * @Route("blog/category/{name}/{page}", name="category_list", requirements={"page" = "\d+"})
    * @Method({"GET", "POST"})
    */
    public function index(Request $request, Category $category, int $page = 1)
    {
        $entityManager = $this->getDoctrine()->getManager();
        
        $results = $entityManager->getRepository(Category::class)->findPostsByCategory($category, $page);
        dump($results);
        $latestPosts = $entityManager->getRepository(Post::class)->findLatest();
        $categories = $entityManager->getRepository(Category::class)->findAll();
        
        $number_of_pages = $this->PaginationUtil->calculateNumberOfPages("category", $category);
        
        $search_form = $this->SearchUtil->createSearchForm();
        if ($search_query = $this->SearchUtil->handleSearchForm($request, $search_form))
        {
            return $this->redirectToRoute('blog_results', array(
                'query' => $search_query,
            ));    
        }
        
        return $this->render('Category/list.html.twig', array(
            'category' => $category,
            'categories' => $categories,
            'posts' => $results,
            'latestPosts' => $latestPosts,
            'search_form' => $search_form->createView(),
            'number_of_pages' => $number_of_pages,
        ));
    }
}
