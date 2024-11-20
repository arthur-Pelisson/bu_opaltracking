<?php

namespace App\Repository;

use App\Entity\ETDLine;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ETDLine|null find($id, $lockMode = null, $lockVersion = null)
 * @method ETDLine|null findOneBy(array $criteria, array $orderBy = null)
 * @method ETDLine[]    findAll()
 * @method ETDLine[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ETDLineRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ETDLine::class);
    }


    /**
     * @param string $navisionDocNo
     * @return int
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getMinLineOfDocNo(string $navisionDocNo): int
    {
        return (int)$this->createQueryBuilder('el')
            ->select('el.navisionLineNo')
            ->where('el.navisionDocNo = :navisionDocNo')
            ->setParameter('navisionDocNo', $navisionDocNo)
            ->orderBy('el.navisionLineNo', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleScalarResult();
    }


    public function getConversationCount(int $etdId): ?array
    {
        $sql = $this->createQueryBuilder('el')
            ->select('el.id', 'count(elc) as countMessages')
            ->join('el.etd', 'e')
            ->join('el.conversation', 'elc')
            ->join('elc.conversationMessages', 'elcm')
            ->where('e.id = :etd_id')
            ->setParameter('etd_id', $etdId)
            ->groupBy('el.id')
            ->getQuery();
        return $sql->getResult(AbstractQuery::HYDRATE_ARRAY);
    }

    /**
     * @param int $etdId
     * @param string|null $vendorNo
     * @return ETDLine[] Returns an array of ETDLine objects
     */
    public function getParentETDLines(int $etdId, string $vendorNo = null): ?array
    {
        $sql = $this->createQueryBuilder('el')
            ->select('el', 'elt', 'ell')
            ->addSelect('elc')
            ->leftJoin('el.etd', 'e')
            ->leftJoin('e.vendor', 'v')
            ->leftJoin('el.etdLineTag', 'elt')
            ->leftJoin('el.childETDLines', 'ell')
            ->leftJoin('el.conversation', 'elc')
            ->where('e.id = :etd_id')
            ->setParameter('etd_id', $etdId)
            ->andWhere('el.parentETDLine is null');

        if ($vendorNo !== null) {
            $sql = $sql
                ->andWhere('v.no = :vendorNo')
                ->setParameter('vendorNo', $vendorNo)
            ;
        }

        return $sql
            ->orderBy('el.itemReference', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
