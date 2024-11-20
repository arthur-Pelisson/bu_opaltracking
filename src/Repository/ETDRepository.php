<?php

namespace App\Repository;

use App\Entity\ETD;
use App\Entity\User;
use App\Type\ETDStatusType;
use App\Type\UserType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ETD|null find($id, $lockMode = null, $lockVersion = null)
 * @method ETD|null findOneBy(array $criteria, array $orderBy = null)
 * @method ETD[]    findAll()
 * @method ETD[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ETDRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ETD::class);
    }


    public function getLastETDsUpdated($userType, $vendorNo = null)
    {
        $sql = $this->createQueryBuilder('e')
            ->select('e')
            ->leftJoin('e.vendor', 'v')
            ->where('e.status = :etdStatus');
//            ->andWhere('e.dateUpdate < ');

        switch ($userType) {
            case UserType::PURCHASER:
                $sql = $sql
                    ->setParameter('etdStatus', ETDStatusType::WAITING_PURCHASER);
                break;
            case UserType::VENDOR:
                $sql = $sql
                    ->setParameter('etdStatus', ETDStatusType::WAITING_VENDOR);
                break;
        }

        if ($vendorNo !== null) {
            $sql = $sql
                ->andWhere('v.no = :vendorNo')
                ->setParameter('vendorNo', $vendorNo);
        }

        return $sql
            ->orderBy('e.dateUpdate', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();
    }
    // /**
    //  * @return ETD[] Returns an array of ETD objects
    //  */
    public function queryFindETDs(User $user, bool $isClosed = false, bool $isOnlyActive = false): Query
    {
        $sql = $this->createQueryBuilder('e')
            ->select(['v.no as vendorNo', 'e.id', 'e.closed', 'e.etdDate', 'e.status', 'e.totalETDLinesCount', 'e.etdChangedETDLinesCount', 'e.qtyChangedETDLinesCount', 'e.shipByChangedETDLinesCount', 'e.notValidatedETDLinesCount', 'count(cm) as countMessages'])
            ->leftJoin('e.vendor', 'v')
            ->leftJoin('e.conversation', 'c')
            ->leftJoin('c.conversationMessages', 'cm')
            ->where('e.closed = :isClosed')
            ->setParameter('isClosed', $isClosed);

        switch ($user->getType()) {
            case UserType::PURCHASER:
                if ($isOnlyActive) {
                    $sql = $sql
                        ->andWhere('e.status = :status')
                        ->setParameter('status', ETDStatusType::WAITING_PURCHASER);
                }
                break;
            case UserType::VENDOR:
                $sql = $sql
                    ->andWhere('v.no = :vendorNo')
                    ->setParameter('vendorNo', $user->getVendor()->getNo());
                if ($isOnlyActive) {
                    $sql = $sql->andWhere('v.startETDDate <= e.etdDate OR v.startETDDate IS NULL')
                        ->andWhere('v.endETDDate >= e.etdDate OR v.endETDDate IS NULL');
                }
                break;
        }

        return $sql
            ->groupBy('e.id')
            ->orderBy('e.etdDate', 'ASC')
            ->getQuery();
    }


    public function findETDById($id): ?ETD
    {
        return $this->createQueryBuilder('e')
            ->select('e', 'v')
            ->leftJoin('e.vendor', 'v')
            ->Where('e.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findETDWithCounts(int $etdId): ?array
    {
        $sql = $this->createQueryBuilder('e')
            ->select(['v.no as vendorNo', 'count(cm) as countMessages', 'e as etd'])
            ->leftJoin('e.vendor', 'v')
            ->leftJoin('e.conversation', 'c')
            ->leftJoin('c.conversationMessages', 'cm')
            ->where('e.id = :etdId')
            ->setParameter('etdId', $etdId)
            ->getQuery()
            ->getOneOrNullResult();
        return $sql;
    }
}
