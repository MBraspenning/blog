<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use App\Entity\Post;
use App\Form\PostType;

class BlogController extends Controller
{
    /**
    * @Route("/", name="blog_index")
    */
    public function index()
    {
        return $this->render('Blog/index.html.twig');
    }
    
    /**
    * @Route("/new", name="blog_create")
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
}
