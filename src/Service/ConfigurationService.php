<?php


namespace App\Service;


use App\Entity\Configuration;
use App\Entity\Conversation;
use App\Entity\ConversationMessage;
use App\Entity\User;
use App\Repository\ConfigurationRepository;
use App\Repository\ConversationMessageRepository;
use App\Repository\ConversationRepository;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

class ConfigurationService
{
    private ConfigurationRepository $_configurationRepository;

    public function __construct(ConfigurationRepository $configurationRepository)
    {
        $this->_configurationRepository = $configurationRepository;
    }

    public function getQtyModifiedToCreatePartial(): int
    {
        $config = $this->_configurationRepository->findOneBy(['name' => 'QTY_MODIFIED_TO_CREATE_PARTIAL']);
        return $config !== null ? $config->getValue(): 0;
    }

    public function getNotificationsFromEmail(): ?string
    {
        $config = $this->_configurationRepository->findOneBy(['name' => 'NOTIFICATION_FROM_EMAIL']);
        return $config !== null ? $config->getValue(): null;
    }

    public function getFormatDate(): ?string
    {
        $config = $this->_configurationRepository->findOneBy(['name' => 'FORMAT_DATE']);
        return $config !== null ? $config->getValue(): null;
    }

    public function getNotificationsToPurchaserEmail(): ?string
    {
        $config = $this->_configurationRepository->findOneBy(['name' => 'NOTIFICATION_TO_PURCHASER_EMAIL']);
        return $config !== null ? $config->getValue(): null;
    }
}