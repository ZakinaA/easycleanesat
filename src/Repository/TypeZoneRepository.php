<?php

namespace App\Repository;

use App\Entity\TypeZone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TypeZoneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypeZone::class);
    }

    public function searchByNomOrDescription(string $query): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT * FROM type_zone WHERE nom LIKE :q OR description LIKE :q ORDER BY nom ASC';
        $result = $conn->executeQuery($sql, ['q' => '%' . $query . '%']);
        return $result->fetchAllAssociative();
    }
}
