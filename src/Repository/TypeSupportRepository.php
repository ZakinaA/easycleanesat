<?php

namespace App\Repository;

use App\Entity\TypeSupport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TypeSupportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypeSupport::class);
    }

    public function searchByNomOrDescription(string $query): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT * FROM type_support WHERE nom LIKE :q OR description LIKE :q ORDER BY nom ASC';
        $result = $conn->executeQuery($sql, ['q' => '%' . $query . '%']);
        return $result->fetchAllAssociative();
    }
}
