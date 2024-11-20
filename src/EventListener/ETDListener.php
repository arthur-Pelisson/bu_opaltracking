<?php


namespace App\EventListener;


use App\Entity\ETD;
use App\Service\ETDService;
use DateTime;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class ETDListener
{
    public function preUpdate(ETD $etd): void
    {
        $etd->setDateUpdate(new DateTime());
    }
}