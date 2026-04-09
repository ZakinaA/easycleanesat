<?php

namespace App\Repository;

use App\Entity\Necessaire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Necessaire>
 */
class NecessaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Necessaire::class);
    }

    public function findAllActif(): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.actif = :actif')
            ->setParameter('actif', true)
            ->orderBy('n.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findAllInactif(): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.actif = :actif')
            ->setParameter('actif', false)
            ->orderBy('n.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
    //     * @return Necessaire[] Returns an array of Necessaire objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('n')
    //            ->andWhere('n.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('n.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Necessaire
    //    {
    //        return $this->createQueryBuilder('n')
    //            ->andWhere('n.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
