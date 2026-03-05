<?php

namespace App\Repository;

use App\Entity\MeoProduit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MeoProduit>
 */
class MeoProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MeoProduit::class);
    }

    public function findAllActif(?int $produitId = null): array
    {
        $queryBuilder = $this->createQueryBuilder('m')
            ->leftJoin('m.produit', 'p')
            ->andWhere('m.actif = :actif')
            ->setParameter('actif', true)
            ->orderBy('m.id', 'ASC');

        if ($produitId !== null) {
            $queryBuilder
                ->andWhere('p.id = :produitId')
                ->setParameter('produitId', $produitId);
        }

        return $queryBuilder
            ->getQuery()
            ->getResult();
    }

    public function findAllInactif(?int $produitId = null): array
    {
        $queryBuilder = $this->createQueryBuilder('m')
            ->leftJoin('m.produit', 'p')
            ->andWhere('m.actif = :actif')
            ->setParameter('actif', false)
            ->orderBy('m.id', 'ASC');

        if ($produitId !== null) {
            $queryBuilder
                ->andWhere('p.id = :produitId')
                ->setParameter('produitId', $produitId);
        }

        return $queryBuilder
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return MeoProduit[] Returns an array of MeoProduit objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('m.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?MeoProduit
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
