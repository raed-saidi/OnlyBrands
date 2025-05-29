<?php

namespace App\Repository;

use App\Entity\Coupon;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CouponRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Coupon::class);
    }

    public function findValidCouponByCode(string $code): ?Coupon
    {
        $qb = $this->createQueryBuilder('c');
        
        return $qb->where('c.code = :code')
            ->andWhere('c.isActive = true')
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findActiveCoupons(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.isActive = true')
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
