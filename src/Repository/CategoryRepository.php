<?php

namespace App\Repository;

use App\Entity\Category;
use App\Utils\Pagination\PaginationParameters;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Category::class);
    }
    
    public function findPostsByCategory(Category $category, int $page)
    {
        $entityManager = $this->getEntityManager();
        
        $pagination_offset = (($page - 1) * PaginationParameters::PaginationMax);
        
        $query = $entityManager->createQuery(
            'SELECT p
            FROM App\Entity\Post p
            JOIN p.categories c
            WHERE c.id = :id
            ORDER BY p.id DESC')
        ->setParameter('id', $category->getId())
        ->setMaxResults(PaginationParameters::PaginationMax)
        ->setFirstResult($pagination_offset);

        // returns an array of Post objects
        return $query->execute();
    }
    
    public function findAllPostsByCategory(Category $category)
    {
        $entityManager = $this->getEntityManager();
                
        $query = $entityManager->createQuery(
            'SELECT p
            FROM App\Entity\Post p
            JOIN p.categories c
            WHERE c.id = :id
            ORDER BY p.id DESC')
        ->setParameter('id', $category->getId());

        // returns an array of Post objects
        return $query->execute();
    }
}
