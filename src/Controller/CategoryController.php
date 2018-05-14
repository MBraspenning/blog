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
use App\Form\DeleteCategoryType;
use App\Service\CategoryService;

class CategoryController extends Controller
{
    private $SearchUtil;
    private $PaginationUtil;
    private $CategoryService;
    
    public function __construct(SearchUtil $SearchUtil, PaginationUtil $PaginationUtil, CategoryService $CategoryService)
    {
        $this->SearchUtil = $SearchUtil;
        $this->PaginationUtil = $PaginationUtil;
        $this->CategoryService = $CategoryService;
    }
    
    /**
    * @Route("blog/category/{name}/{page}", name="category_list", requirements={"page" = "\d+", "name" = "^(?!admin)(?!new)[^/]+"})
    * @Method({"GET", "POST"})
    */
    public function index(Request $request, Category $category, int $page = 1)
    {
        $entityManager = $this->getDoctrine()->getManager();
        
        $results = $entityManager->getRepository(Category::class)->findPostsByCategory($category, $page);
        
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
            
            $this->CategoryService->persist($category);
            
            return $this->redirectToRoute('blog_index');
        }
        
        return $this->render('Category/new.html.twig', array(
            'form' => $form->createView(),
        ));
    }
    
    /**
    * @Route("blog/category/admin", name="category_admin")
    * @Security("has_role('ROLE_ADMIN')")
    * @Method({"GET", "POST"})
    */
    public function admin(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        
        $categories = $entityManager->getRepository(Category::class)->findAll();
        
        $delete_forms = [];
        
        foreach ($categories as $category)
        {
            $delete_forms[] = $this->createForm(DeleteCategoryType::class, $category, array(
                'action' => $this->generateUrl('category_delete', array(
                    'name' => $category->getName(),
                )),
                'method' => 'DELETE',
            ))->createView();  
        }
        
        return $this->render('Category/admin.html.twig', array(
            'categories' => $categories,
            'delete_forms' => $delete_forms,
        ));
    }
    
//    /**
//    * @Route("blog/category/admin/{id}", name="category_admin")
//    * @Security("has_role('ROLE_ADMIN')")
//    * @Method({"GET", "POST"})
//    */
//    public function show(Request $request, Category $category)
//    {
//        $delete_form = $this->createForm(DeleteCategoryType::class, $category, array(
//                'action' => $this->generateUrl('category_delete', array(
//                    'id' => $category->getId(),
//                )),
//                'method' => 'DELETE',
//            ));
//        
//        return $this->render('Category/show.html.twig', array(
//            'category' => $category,
//            'delete_form' => $delete_form->createView(),
//        ));
//    }
    
    /**
    * @Route("blog/category/{name}", name="category_delete")
    * @Security("has_role('ROLE_ADMIN')")
    * @Method({"DELETE"})
    */
    public function delete(Request $request, Category $category)
    {
        $form = $this->createForm(DeleteCategoryType::class, $category, array(
            'action' => $this->generateUrl('category_delete', array(
                'name' => $category->getName(),
            )),
            'method' => 'DELETE',
        ));
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid())
        {
            $this->CategoryService->remove($category);
        }
        
        return $this->redirectToRoute('blog_index');
    }
}
