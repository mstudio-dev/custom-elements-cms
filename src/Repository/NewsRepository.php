<?php

namespace App\Repository;

use App\Entity\News;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<News>
 */
class NewsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, News::class);
    }

    /**
     * Find all published news ordered by publication date
     */
    public function findPublished(?int $limit = null): array
    {
        $qb = $this->createQueryBuilder('n')
            ->where('n.status = :status')
            ->andWhere('n.publishedAt IS NOT NULL')
            ->andWhere('n.publishedAt <= :now')
            ->setParameter('status', 'published')
            ->setParameter('now', new \DateTime())
            ->orderBy('n.publishedAt', 'DESC');

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Find featured published news
     */
    public function findFeatured(?int $limit = 3): array
    {
        return $this->createQueryBuilder('n')
            ->where('n.status = :status')
            ->andWhere('n.featured = :featured')
            ->andWhere('n.publishedAt IS NOT NULL')
            ->andWhere('n.publishedAt <= :now')
            ->setParameter('status', 'published')
            ->setParameter('featured', true)
            ->setParameter('now', new \DateTime())
            ->orderBy('n.publishedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find one published news by slug
     */
    public function findOnePublishedBySlug(string $slug): ?News
    {
        return $this->createQueryBuilder('n')
            ->where('n.slug = :slug')
            ->andWhere('n.status = :status')
            ->andWhere('n.publishedAt IS NOT NULL')
            ->andWhere('n.publishedAt <= :now')
            ->setParameter('slug', $slug)
            ->setParameter('status', 'published')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getOneOrNullResult();
    }
}
