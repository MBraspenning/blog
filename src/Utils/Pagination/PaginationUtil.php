<?php

namespace App\Utils\Pagination;

use App\Entity\Post;
use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class PaginationUtil
{ 
    protected $repository;
    protected $entityManager;
    
    public function __construct(RegistryInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Post::class);
        $this->entityManager = $entityManager;
    }
    
    public function findPaginated($page)
    {
        $pagination_offset = ($page - 1) * PaginationParameters::PaginationMax;
        
        $query = $this->repository->createQueryBuilder('post')
            ->setMaxResults(PaginationParameters::PaginationMax)
            ->setFirstResult($pagination_offset)
            ->orderBy('post.id', 'DESC')
            ->getQuery();
        
        return $query->getResult();
    }
    
    public function calculateNumberOfPages(string $asking_page, $query = NULL)
    {   
        if ($asking_page === "index") 
        {
            return ceil(count($this->repository->findAll()) / PaginationParameters::PaginationMax);        
        }
        if ($asking_page === "results")
        {
            return ceil(count($this->repository->findAllBasedOnSearchQuery($query)) / PaginationParameters::PaginationMax);
        }
        if ($asking_page === "category")
        {
            return ceil(count($this->entityManager->getRepository(Category::class)->findAllPostsByCategory($query)) / PaginationParameters::PaginationMax);
        }
    } 
}