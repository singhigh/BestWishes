<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * GiftListRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class GiftListRepository extends EntityRepository
{
    public function findFullBySlug($slug) {
        return $this->createQueryBuilder('l')
            ->where('l.slug = :slug')
            ->leftJoin('l.categories', 'c')
            ->leftJoin('c.gifts', 'g')
            ->addSelect('c')
            ->addSelect('g')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->useQueryCache(true)
            ->useResultCache(true)
            ->setResultCacheLifetime(600)
            ->getOneOrNullResult();
    }
}
