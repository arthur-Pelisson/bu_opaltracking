<?php

namespace App\Repository;

use App\Entity\ConversationMessage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ConversationMessage|null find($id, $lockMode = null, $lockVersion = null)
 * @method ConversationMessage|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConversationMessage[]    findAll()
 * @method ConversationMessage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConversationMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConversationMessage::class);
    }

    // /**
    //  * @return ConversationMessage[] Returns an array of ConversationMessage objects
    //  */
    public function getConversationMessages($id, bool $isEtdLine = false)
    {
        $sql = $this->createQueryBuilder('cm')
//        ->select(['cm.dateAdd', 'cm.content', 'u.code', 'u.type'])
        ->select('cm')
            ->addSelect('u')
        ->leftJoin('cm.conversation', 'c')
        ->leftJoin('cm.writeByUser', 'u');

        if($isEtdLine)
        {
            $sql = $sql->where('c.etdLine = :id');
        } else {
            $sql = $sql->where('c.etd = :id');
        }

        return $sql
            ->setParameter('id', $id)
            ->orderBy('cm.dateAdd', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
