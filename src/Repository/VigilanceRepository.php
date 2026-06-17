<?php

namespace App\Repository;

use App\Entity\Vigilance;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vigilance>
 */
class VigilanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vigilance::class);
    }

    /**
     * @return Vigilance[] Returns an array of active Vigilance objects
     */
    public function findAllActif(): array
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.actif = :val')
            ->setParameter('val', true)
            ->orderBy('v.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Vigilance[] Returns an array of inactive Vigilance objects
     */
    public function findAllInactif(): array
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.actif = :val')
            ->setParameter('val', false)
            ->orderBy('v.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    //    /**
    //     * @return Vigilance[] Returns an array of Vigilance objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('v.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Vigilance
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
