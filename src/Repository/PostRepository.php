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
    
    public function findPostByDateAndSlug($date, $slug)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.date_added = :date')
            ->setParameter('date', $date)
            ->andWhere('p.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
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
            'SELECT p.Title
            FROM App\Entity\Post p
            WHERE p.Body LIKE :searchquery
            OR p.introduction LIKE :searchquery
            OR p.Title LIKE :searchquery
            ORDER BY p.id DESC'
        )->setParameter('searchquery', '%'.$searchquery.'%');

        // returns an array of Post objects
        return $query->execute();
    }
}
