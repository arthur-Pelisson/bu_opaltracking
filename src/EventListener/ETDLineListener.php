<?php


namespace App\EventListener;


use App\Entity\ETD;
use App\Entity\ETDLine;
use App\Entity\ETDLineHistory;
use App\Type\ETDLineStatusType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\UnitOfWork;

class ETDLineListener
{
    public function preUpdate(ETDLine $etdLine): void
    {
        $etdLine->setExportedToNavision(false);
        $etdLine->setDateUpdate(new DateTime());
    }
}