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
use App\Form\PostType;
use App\Form\DeletePostType;

class BlogController extends Controller
{
    /**
    * @Route("/blog", name="blog_index")
    * @Method("GET")
    */
    public function index()
    {
        $entityManager = $this->getDoctrine()->getManager();
        
        $posts = $entityManager->getRepository(Post::class)->findAllOrderLatestFirst();
        $latestPosts = $entityManager->getRepository(Post::class)->findLatest();
        
        return $this->render('Blog/index.html.twig', array(
            'posts' => $posts,
            'latestPosts' => $latestPosts,
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
            $slug = strtolower($form->getData()->getTitle());
            $slug = preg_replace("/[^a-z0-9_\s-]/", "", $slug);
            $slug = preg_replace("/[\s_]/", "-", $slug);
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
    * @Route("/blog/{date_added}/{slug}", name="blog_show", requirements={"date_added" = ".+", "date_added" = "^(?!edit).+"})
    * @ParamConverter("date_added", options={"format": "Y/m/d"})
    * @Method("GET")
    */
    public function show(Post $post, \DateTime $date_added)
    {        
        $deleteForm = $this->createForm(DeletePostType::class, $post, array(
            'action' => $this->generateUrl('blog_delete', array(
                'slug' => $post->getSlug(),
            )),
            'method' => 'DELETE',
        ));
        
        $entityManager = $this->getDoctrine()->getManager();
        $latestPosts = $entityManager->getRepository(Post::class)->findLatest();
        
        return $this->render('Blog/show.html.twig', array(
            'post' => $post,
            'delete_form' => $deleteForm->createView(),
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
}
