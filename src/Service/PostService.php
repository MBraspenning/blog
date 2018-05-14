<?php

namespace App\Service;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;

class PostService
{
    private $repository;
    private $entityManager;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Post::class);
        $this->entityManager = $entityManager;        
    }
    
    public function persist(Post $post)
    {
        $this->entityManager->persist($post);
        $this->entityManager->flush();
    }
    
    public function remove(Post $post)
    {
        $this->entityManager->remove($post);
        $this->entityManager->flush();
    }
}