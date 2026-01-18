<?php

namespace App\Repository;

use App\Entity\Media;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Media>
 */
class MediaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Media::class);
    }

    /**
     * Findet alle Ordner (ohne Dateien)
     */
    public function findAllFolders(): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.type = :type')
            ->setParameter('type', 'folder')
            ->orderBy('m.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Findet alle Medien in einem bestimmten Ordner
     */
    public function findByParent(?Media $parent = null): array
    {
        $qb = $this->createQueryBuilder('m');
        
        if ($parent === null) {
            $qb->where('m.parent IS NULL');
        } else {
            $qb->where('m.parent = :parent')
               ->setParameter('parent', $parent);
        }
        
        return $qb->orderBy('m.type', 'ASC')
                  ->addOrderBy('m.sorting', 'ASC')
                  ->addOrderBy('m.name', 'ASC')
                  ->getQuery()
                  ->getResult();
    }

    /**
     * Findet alle Bilder
     */
    public function findAllImages(): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.type = :type')
            ->andWhere('m.mimeType LIKE :mime')
            ->setParameter('type', 'file')
            ->setParameter('mime', 'image/%')
            ->orderBy('m.uploadedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
