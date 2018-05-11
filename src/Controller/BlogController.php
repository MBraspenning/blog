<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use App\Entity\Post;
use App\Entity\Category;
use App\Form\PostType;
use App\Form\DeletePostType;
use App\Form\SearchType;
use App\Utils\Pagination\PaginationUtil;
use App\Utils\Slugger\SluggerUtil;
use App\Utils\Search\SearchUtil;

class BlogController extends Controller
{
    private $PaginationUtil;
    private $SluggerUtil;
    private $SearchUtil;
    
    public function __construct(PaginationUtil $PaginationUtil, SluggerUtil $SluggerUtil, SearchUtil $SearchUtil)
    {
        $this->PaginationUtil = $PaginationUtil;
        $this->SluggerUtil = $SluggerUtil;
        $this->SearchUtil = $SearchUtil;
    }
    
    /**
    * @Route("/blog/{page}", name="blog_index", requirements={"page" = "\d+"})
    * @Method({"GET", "POST"})
    */
    public function index(Request $request, int $page = 1)
    {
        $entityManager = $this->getDoctrine()->getManager();
        
        $posts = $this->PaginationUtil->findPaginated($page);
        $categories = $entityManager->getRepository(Category::class)->findAll();
        $latestPosts = $entityManager->getRepository(Post::class)->findLatest();
        
        $number_of_pages = $this->PaginationUtil->calculateNumberOfPages("index");
        
        $search_form = $this->SearchUtil->createSearchForm();
        if ($search_query = $this->SearchUtil->handleSearchForm($request, $search_form))
        {
            return $this->redirectToRoute('blog_results', array(
                'query' => $search_query,
            ));    
        }

        return $this->render('Blog/index.html.twig', array(
            'posts' => $posts,
            'categories' => $categories,
            'latestPosts' => $latestPosts,
            'search_form' => $search_form->createView(),
            'number_of_pages' => $number_of_pages,
        ));
    }
    
    /**
    * @Route("/blog/new", name="blog_create")
    * @Security("has_role('ROLE_ADMIN')")
    * @Method({"GET", "POST"})
    */
    public function create(Request $request)
    {                
        $post = new Post();
        
        $post->setDateAdded(new \DateTime(date('Y-m-d')));
        
        $form = $this->createForm(PostType::class, $post);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid())
        {
            $slug = $this->SluggerUtil->makeSlug($form->getData()->getTitle());
            $post->setSlug($slug);
            
            $post = $form->getData();
            
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($post);
            $entityManager->flush();
            
            return $this->redirectToRoute('blog_index');
        }
        
        return $this->render('Blog/new.html.twig', array(
            'form' => $form->createView(),
        ));
    }
    
    /**
    * @Route("/blog/{date_added}/{slug}", 
        name="blog_show", 
        requirements={"date_added" = ".+", "date_added" = "^(?!edit)(?!results)(?!category).+"})
    * @ParamConverter("date_added", options={"format": "Y/m/d"})
    * @Method({"GET", "POST"})
    */
    public function show(Request $request, Post $post, \DateTime $date_added)
    {        
        $deleteForm = $this->createForm(DeletePostType::class, $post, array(
            'action' => $this->generateUrl('blog_delete', array(
                'slug' => $post->getSlug(),
            )),
            'method' => 'DELETE',
        ));
        
        $search_form = $this->SearchUtil->createSearchForm();
        if ($search_query = $this->SearchUtil->handleSearchForm($request, $search_form))
        {
            return $this->redirectToRoute('blog_results', array(
                'query' => $search_query,
            ));    
        }
        
        $entityManager = $this->getDoctrine()->getManager();
        $latestPosts = $entityManager->getRepository(Post::class)->findLatest();
        $categories = $entityManager->getRepository(Category::class)->findAll();
        
        return $this->render('Blog/show.html.twig', array(
            'post' => $post,
            'categories' => $categories,
            'delete_form' => $deleteForm->createView(),
            'search_form' => $search_form->createView(),
            'latestPosts' => $latestPosts,
        ));
    }
    
    /**
    * @Route("/blog/edit/{date_added}/{slug}", name="blog_edit", requirements={"date_added" = ".+"})
    * @ParamConverter("date_added", options={"format": "Y/m/d"})
    * @Security("has_role('ROLE_ADMIN')")
    * @Method({"GET", "POST"})
    */
    public function edit(Request $request, Post $post, \DateTime $date_added)
    {
        $form = $this->createForm(PostType::class, $post);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid())
        {
            $slug = strtolower($form->getData()->getTitle());
            $slug = preg_replace("/[^a-z0-9_\s-]/", "", $slug);
            $slug = preg_replace("/[\s_]/", "-", $slug);
            $post->setSlug($slug);
            
            $post = $form->getData();
            
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($post);
            $entityManager->flush();
            
            return $this->redirectToRoute('blog_show', array(
                'date_added' => $post->getDateAdded()->format('Y/m/d'),
                'slug' => $post->getSlug(),
            ));
        }
        
        return $this->render('Blog/edit.html.twig', array(
            'post' => $post,
            'form' => $form->createView(),
        ));
    }
    
    /**
    * @Route("/blog/{slug}", name="blog_delete")
    * @Security("has_role('ROLE_ADMIN')")
    * @Method({"DELETE"})
    */
    public function delete(Request $request, Post $post)
    {
        $form = $this->createForm(DeletePostType::class, $post, array(
            'action' => $this->generateUrl('blog_delete', array(
                'slug' => $post->getSlug(),
            )),
            'method' => 'DELETE',
        ));
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid())
        {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($post);
            $entityManager->flush();
        }
        
        return $this->redirectToRoute('blog_index');
    }
    
    /**
    * @Route("/blog/results/{query}/{page}", name="blog_results", requirements={"page" = "\d+"})
    * @Method({"GET", "POST"})
    */
    public function results(Request $request, string $query, int $page = 1)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $latestPosts = $entityManager->getRepository(Post::class)->findLatest();
        $categories = $entityManager->getRepository(Category::class)->findAll();
        
        // Change to findBasedOnSearchQuery to include pagination 
        // Don't forget to remove 'set number_of_pages = 0' in twig
        $results = $entityManager->getRepository(Post::class)->findAllBasedOnSearchQuery($query);
        
        $number_of_pages = $this->PaginationUtil->calculateNumberOfPages("results", $query);
        
        $search_form = $this->SearchUtil->createSearchForm();
        if ($search_query = $this->SearchUtil->handleSearchForm($request, $search_form))
        {
            return $this->redirectToRoute('blog_results', array(
                'query' => $search_query,
            ));    
        }
        
        $no_results_message = "";
        
        if (count($results) === 0) 
        {
            $no_results_message = "No results found for \"" . $query . "\". Try again?";
            
            return $this->render('Blog/noresults.html.twig', array(
                'no_results_message' => $no_results_message,
                'search_query' => $query,
                'search_form' => $search_form->createView(),
                'search_form_2' => $search_form->createView(),
                'latestPosts' => $latestPosts,
                'categories' => $categories,
            ));
        }

        return $this->render('Blog/results.html.twig', array(
            'search_query' => $query,
            'posts' => $results,
            'search_form' => $search_form->createView(),
            'latestPosts' => $latestPosts,
            'categories' => $categories,
            'number_of_pages' => $number_of_pages,
        ));
    }
}
