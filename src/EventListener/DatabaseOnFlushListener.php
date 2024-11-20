<?php


namespace App\EventListener;


use App\Entity\ETD;
use App\Entity\ETDLine;
use App\Entity\ETDLineHistory;
use App\Entity\ETDLineTag;
use App\Entity\ETDLineTagHistory;
use App\Type\ETDLineStatusType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;

class DatabaseOnFlushListener
{
    /**
     * @param OnFlushEventArgs $eventArgs
     */
    public function onFlush(OnFlushEventArgs $eventArgs): void
    {
//        $em = $eventArgs->getEntityManager();
//        $uow = $em->getUnitOfWork();
//        dump($uow->getScheduledEntityUpdates());
    }
}