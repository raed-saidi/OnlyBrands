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
        $now = new \DateTime();
        
        return $this->createQueryBuilder('c')
            ->where('c.code = :code')
            ->andWhere('c.isActive = :active')
            ->andWhere('(c.validFrom IS NULL OR c.validFrom <= :now)')
            ->andWhere('(c.validTo IS NULL OR c.validTo >= :now)')
            ->andWhere('(c.usageLimit IS NULL OR c.usageCount < c.usageLimit)')
            ->setParameter('code', $code)
            ->setParameter('active', true)
            ->setParameter('now', $now)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findActiveCoupons(): array
    {
        $now = new \DateTime();
        
        return $this->createQueryBuilder('c')
            ->where('c.isActive = :active')
            ->andWhere('(c.validFrom IS NULL OR c.validFrom <= :now)')
            ->andWhere('(c.validTo IS NULL OR c.validTo >= :now)')
            ->andWhere('(c.usageLimit IS NULL OR c.usageCount < c.usageLimit)')
            ->setParameter('active', true)
            ->setParameter('now', $now)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}