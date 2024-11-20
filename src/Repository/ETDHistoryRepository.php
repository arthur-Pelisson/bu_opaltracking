<?php

namespace App\Repository;

use App\Entity\ETDHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ETDHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method ETDHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method ETDHistory[]    findAll()
 * @method ETDHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ETDHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ETDHistory::class);
    }
}
