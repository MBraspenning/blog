<?php

namespace App\Utils\Pagination;

use App\Entity\Post;
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
    
    public function calculateNumberOfPages()
    {
        return ceil(count($this->repository->findAll()) / PaginationParameters::PaginationMax);    
    } 
}