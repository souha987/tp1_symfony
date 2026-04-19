<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

public function findPublishedArticles(): array
{
    return $this->createQueryBuilder('a')
        ->where('a.publie = :p')
        ->setParameter('p', true)
        ->orderBy('a.dateCreation', 'DESC')
        ->getQuery()
        ->getResult();
}
public function findPublished()
{
    return $this->createQueryBuilder('a')
        ->where('a.publie = :val')
        ->setParameter('val', true)
        ->orderBy('a.id', 'DESC')
        ->getQuery()
        ->getResult();
}
public function findLatest(int $limit = 3): array
{
    return $this->createQueryBuilder('a')
        ->orderBy('a.dateCreation', 'DESC')
        ->setMaxResults($limit)
        ->getQuery()
        ->getResult();
}
}