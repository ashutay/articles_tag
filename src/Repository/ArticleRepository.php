<?php

namespace App\Repository;

use App\Entity\Article;
use App\Entity\ArticleTag;
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

    public function findByTags(array $tagNames)
    {
        $qb = $this->createQueryBuilder('a')
            ->innerJoin(ArticleTag::class, 'at', 'WITH', 'at.article = a.id')
            ->innerJoin('at.tag', 't', 'WITH', 't.id = at.tag')
            ->where('t.name IN (:tagNames)')
            ->groupBy('a.id')
            ->having('COUNT(t.id) = :tagCount')
            ->setParameter('tagNames', $tagNames)
            ->setParameter('tagCount', count($tagNames));

        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return Article[] Returns an array of Article objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Article
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
