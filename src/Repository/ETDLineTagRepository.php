<?php

namespace App\Repository;

use App\Entity\ETDLineTag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ETDLineTag|null find($id, $lockMode = null, $lockVersion = null)
 * @method ETDLineTag|null findOneBy(array $criteria, array $orderBy = null)
 * @method ETDLineTag[]    findAll()
 * @method ETDLineTag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ETDLineTagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ETDLineTag::class);
    }
}
