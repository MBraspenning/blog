<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Post::class);
    }
    
    public function findPaginated($page)
    {
        $pagination_max = 5;
        $pagination_offset = ($page - 1) * $pagination_max;
        
        $query = $this->createQueryBuilder('post')
            ->setMaxResults($pagination_max)
            ->setFirstResult($pagination_offset)
            ->orderBy('post.id', 'DESC')
            ->getQuery();
        
        return $query->getResult();
    }
    
    public function findLatest()
    {
        return $this->findBy(array(), array('id' => 'DESC'), 5);
    }
    
    public function findAllOrderLatestFirst()
    {
        return $this->findBy(array(), array('id' => 'DESC'));
    }
    
    public function findBasedOnSearchQuery(string $searchquery)
    {
        $entityManager = $this->getEntityManager();
        
        $query = $entityManager->createQuery(
            'SELECT p
            FROM App\Entity\Post p
            JOIN p.categories c
            WHERE p.body LIKE :searchquery
            OR p.introduction LIKE :searchquery
            OR p.title LIKE :searchquery
            OR c.name LIKE :searchquery
            ORDER BY p.id DESC'
        )->setParameter('searchquery', '%'.$searchquery.'%');

        // returns an array of Post objects
        return $query->execute();
    }
}
