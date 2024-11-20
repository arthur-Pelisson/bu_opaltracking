<?php


namespace App\Service;


use App\Dto\UpdateETDLinesMessageCounters;
use App\Entity\Conversation;
use App\Entity\ConversationMessage;
use App\Entity\ETD;
use App\Entity\ETDLine;
use App\Entity\User;
use App\Repository\ConversationMessageRepository;
use App\Repository\ConversationRepository;
use App\Tool\StringTools;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

class ConversationService
{
    private ConversationRepository $_conversationRepository;
    private ConversationMessageRepository $_conversationMessageRepository;
    private EntityManagerInterface $_entityManager;

    public function __construct(ConversationRepository $conversationRepository, ConversationMessageRepository $conversationMessageRepository, EntityManagerInterface $entityManager)
    {
        $this->_conversationRepository = $conversationRepository;
        $this->_conversationMessageRepository = $conversationMessageRepository;
        $this->_entityManager = $entityManager;
    }


    public function getConversationMessagesByETDId(int $etdId)
    {
        return $this->_conversationMessageRepository->getConversationMessages($etdId, false);
    }

    public function getConversationMessagesByETDLineId(int $etdLineId)
    {
        return $this->_conversationMessageRepository->getConversationMessages($etdLineId, true);
    }


    public function addETDConversationMessage(ETD $etd, User $user, ?string $messageContent, UpdateETDLinesMessageCounters $counters = null): bool
    {
        $conversation = $this->_conversationRepository->findOneBy(['etd' => $etd->getId()]);

        if($conversation === null) {
            $conversation = new Conversation();
            $conversation->setEtd($etd);
            $this->_entityManager->persist($conversation);
        }

        return $this->addConversationMessage($user, $messageContent, $conversation, $counters);
    }

    public function addETDLineConversationMessage(ETDLine $etdLine, User $user, string $messageContent): bool
    {
        $conversation = $this->_conversationRepository->findOneBy(['etdLine' => $etdLine->getId()]);

        if($conversation === null) {
            $conversation = new Conversation();
            $conversation->setEtdLine($etdLine);
            $this->_entityManager->persist($conversation);
        }

        return $this->addConversationMessage($user, $messageContent, $conversation);
    }

    public function addConversationMessage(User $user, ?string $messageContent, Conversation $conversation, UpdateETDLinesMessageCounters $counters = null): bool
    {
        if(StringTools::is_null_or_empty($messageContent)) {
            $messageContent = null;
        }

        $conversationMessage = new ConversationMessage();
        $conversationMessage->setDateAdd(new DateTime());
        $conversationMessage->setContent($messageContent);
        $conversationMessage->setConversation($conversation);
        $conversationMessage->setWriteByUser($user);

        if($counters !== null) {
            $conversationMessage->setCountApprovedChanged($counters->getCountApprovedChanged());
            $conversationMessage->setCountRejectedChanged($counters->getCountRejectedChanged());
            $conversationMessage->setCountValidateETDDateChanged($counters->getCountValidateETDDateChanged());
            $conversationMessage->setCountValidateQtyChanged($counters->getCountValidateQtyChanged());
            $conversationMessage->setCountValidateShipByChanged($counters->getCountValidateShipByChanged());
        }

        $this->_entityManager->persist($conversationMessage);
        return true;
    }
}