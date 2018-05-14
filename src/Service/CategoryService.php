<?php

namespace App\Service;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;

class CategoryService 
{
    private $repository;
    private $entityManager;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Category::class);
        $this->entityManager = $entityManager;        
    }
    
    public function persist(Category $category)
    {
        $this->entityManager->persist($category);
        $this->entityManager->flush();
    }
    
    public function remove(Category $category)
    {
        $this->entityManager->remove($category);
        $this->entityManager->flush();
    }
}