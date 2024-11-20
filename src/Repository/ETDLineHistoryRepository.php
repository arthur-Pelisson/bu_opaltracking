<?php

namespace App\Repository;

use App\Entity\ETDLineHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ETDLineHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method ETDLineHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method ETDLineHistory[]    findAll()
 * @method ETDLineHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ETDLineHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ETDLineHistory::class);
    }
}
