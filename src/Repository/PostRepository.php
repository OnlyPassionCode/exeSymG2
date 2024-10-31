<?php

namespace App\Repository;

use App\Entity\Post;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use function Symfony\Component\String\u;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function findPublishedPostsBySection(int $sectionId)
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.sections', 's')
            ->where('s.id = :sectionId')
            ->andWhere('p.postPublished = true')
            ->setParameter('sectionId', $sectionId)
            ->orderBy('p.postDatePublished', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findPublishedPostsByUser(int $userId)
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.user', 'u')
            ->where('u.id = :userId')
            ->andWhere('p.postPublished = true')
            ->setParameter('userId', $userId)
            ->orderBy('p.postDateCreated', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findAllPostsByUser(int $userId)
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.user', 'u')
            ->where('u.id = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('p.postDateCreated', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findTenLastPublishedPosts()
    {
        return $this->createQueryBuilder('p')
            ->where('p.postPublished = true')
            ->orderBy('p.postDatePublished', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }



    //    /**
    //     * @return Post[] Returns an array of Post objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Post
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
