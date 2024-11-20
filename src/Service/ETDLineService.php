<?php


namespace App\Service;


use App\Dto\PartialMessage;
use App\Dto\UpdateETDLinesMessageCounters;
use App\Entity\ETD;
use App\Entity\ETDLine;
use App\Entity\ETDLineHistory;
use App\Entity\ETDLineTag;
use App\Entity\ETDLineTagHistory;
use App\Entity\User;
use App\Repository\ETDLineRepository;
use App\Tool\StringTools;
use App\Type\ETDLineStatusType;
use App\Type\ETDStatusType;
use App\Type\UserType;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use http\Exception\InvalidArgumentException;
use UnexpectedValueException;

class ETDLineService
{
    private const NAVISION_LINE_NO_SEPARATOR = 10000;
    private ETDLineRepository $_etdLineRepository;
    private ETDService $_etdService;
    private ConversationService $_conversationService;
    private EntityManagerInterface $_entityManager;

    public function __construct(ETDLineRepository $etdLineRepository, ETDService $etdService, ConversationService $conversationService, EntityManagerInterface $entityManager)
    {
        $this->_etdLineRepository = $etdLineRepository;
        $this->_etdService = $etdService;
        $this->_conversationService = $conversationService;
        $this->_entityManager = $entityManager;
    }

    public function getETDLinesAsCSV(ETD $etd, User $user): string
    {
        /** @var ETDLine[] $etdLines */
        $etdLines = $this->getParentETDLines($user, $etd->getId());
        $csvContent = implode(';', $this->getHeadersToExport()) . ';' . PHP_EOL;
        foreach ($etdLines as $etdLine) {
            $fieldsToExport = $this->getFieldsToExport($etdLine);
            $csvContent .= implode(';', $fieldsToExport) . ';' . PHP_EOL;
            if ($etdLine->getChildETDLines()) {
                foreach ($etdLine->getChildETDLines() as $child) {
                    $fieldsToExport = $this->getFieldsToExport($child);
                    $csvContent .= implode(';', $fieldsToExport) . ';' . PHP_EOL;
                }
            }
        }
        return $csvContent;
    }

    public function getParentETDLines(User $user, int $etdId): array
    {
        switch ($user->getType()) {
            case UserType::PURCHASER:
                return $this->_etdLineRepository->getParentETDLines($etdId);
            case UserType::VENDOR:
                return $this->_etdLineRepository->getParentETDLines($etdId, $user->getVendor()->getNo());
            default:
                throw new UnexpectedValueException('This userType is not handled');
        }
    }

    private function getHeadersToExport(): array
    {
        $fieldsToExport = [];
        $fieldsToExport[] = 'Status';
        $fieldsToExport[] = 'PO Number';
        $fieldsToExport[] = 'PI Number';
        $fieldsToExport[] = 'Item Reference';
        $fieldsToExport[] = 'Outstanding Qty';
        $fieldsToExport[] = 'Delivery Date';
        $fieldsToExport[] = 'Ship By';
        $fieldsToExport[] = 'Qty Confirmed';
        $fieldsToExport[] = 'ETD Confirmed';
        $fieldsToExport[] = 'Ship By Confirmed';
        $fieldsToExport[] = 'Order Type';
        return $fieldsToExport;
    }

    private function getFieldsToExport(ETDLine $etdLine): array
    {
        $fieldsToExport = [];
        $fieldsToExport[] = $etdLine->getStatus();
        $fieldsToExport[] = $etdLine->getNavisionDocNo();
        $fieldsToExport[] = $etdLine->getPiNo();
        $fieldsToExport[] = $etdLine->getItemReference();
        $fieldsToExport[] = $etdLine->getOutstandingQty();
        $fieldsToExport[] = $etdLine->getDeliveryDate() ? $etdLine->getDeliveryDate()->format('Y-m-d') : null;
        $fieldsToExport[] = $etdLine->getShipBy();
        $fieldsToExport[] = $etdLine->getOutstandingQtyConfirmed();
        $fieldsToExport[] = $etdLine->getEtdDateConfirmed() ? $etdLine->getEtdDateConfirmed()->format('Y-m-d') : null;
        $fieldsToExport[] = $etdLine->getShipByConfirmed();
        $fieldsToExport[] = $etdLine->getOrderType();
        return $fieldsToExport;
    }

    /**
     * @throws NonUniqueResultException
     * @throws Exception
     * @throws NoResultException
     */
    public function updateETDLines(User $user, array $etdLinesUpdated, int $etdId, ?string $contentMessage, UpdateETDLinesMessageCounters $counters, array $partialMessages): ?ETD
    {
        $etd = $this->_etdService->findETDById($etdId);
        if ($etd === null || !$this->_etdService->canUserUpdateETD($etd, $user)) {
            return null;
        }

        /** @var ETDLine[] $etdLines */
        $etdLines = $this->getParentETDLines($user, $etdId);

        $docNoCreated = [];

        /** @var ETDLine[] $etdLinesUpdated */
        foreach ($etdLinesUpdated as $etdLineFromRequest) {
            // On bosse uniquement sur les ETDLineParent
            if ($etdLineFromRequest->getParentETDLine() !== null) {
                continue;
            }

            $etdLine = null;
            foreach ($etdLines as $element) {
                if ($element->getNavisionDocNo() === $etdLineFromRequest->getNavisionDocNo() && $element->getItemReference() === $etdLineFromRequest->getItemReference()) {
                    $etdLine = $element;
                    break;
                }
            }

            if ($etdLine === null) {
                throw new Exception('ETDLine ' . $etdLineFromRequest->getNavisionDocNo() . '/' . $etdLineFromRequest->getItemReference() . ' does not exist');
            }

            $etd->addEtdLine($etdLine);
            if ($etdLine->getReadOnly()) {
                continue;
            }

            // @read : https://www.doctrine-project.org/projects/doctrine-orm/en/2.8/reference/transactions-and-concurrency.html
            if(array_key_exists($etdLine->getNavisionDocNo(), $docNoCreated)) {
                $docNo = $docNoCreated[$etdLine->getNavisionDocNo()];
            } else {
                $docNo = $this->_etdLineRepository->getMinLineOfDocNo($etdLine->getNavisionDocNo());
                if ($docNo > 0) {
                    $docNo = 0;
                }
            }


            if ($this->isETDLineUpdated($etdLine, $etdLineFromRequest)) {
                $etdLine = $this->updateETDLineFromETDLineRequest($etdLine, $etdLineFromRequest);
                $this->_entityManager->persist($this->createETDLineHistory($etdLine));
                $this->_entityManager->persist($this->createETDLineTagHistory($etdLine->getEtdLineTag()));
            }
            $childsFromRequest = $etdLineFromRequest->getChildETDLines();

            $found_key = array_search($etdLine->getId(), array_column($partialMessages, 'id'), true);
            $partialMessage = $found_key !== false ? $partialMessages[$found_key] : null;

            $etdLineChilds = $etdLine->getChildETDLines();
            foreach ($etdLineChilds as $etdLineChild) {
                $doesChildExists = false;
                foreach ($childsFromRequest as $childFromRequest) {
                    if ($etdLineChild->getNavisionDocNo() === $childFromRequest->getNavisionDocNo() &&
                        $etdLineChild->getNavisionLineNo() === $childFromRequest->getNavisionLineNo()) {
                        $doesChildExists = true;
                        if ($this->isETDLineUpdated($etdLineChild, $childFromRequest)) {
                            $this->updateETDLineFromETDLineRequest($etdLineChild, $childFromRequest);
                            $this->_entityManager->persist($this->createETDLineHistory($etdLineChild));
                            $this->_entityManager->persist($this->createETDLineTagHistory($etdLineChild->getEtdLineTag()));
                        }
                        $this->updateETDLineStatusFromETDLineRequest($etdLineChild, $childFromRequest, $user);
                        $this->addPartialMessageAsConversationMessage($etdLineChild, $user, $partialMessage);
                        break;
                    }
                }
                if (!$doesChildExists) {
                    $etd->removeEtdLine($etdLineChild);
                    $this->_entityManager->remove($etdLineChild);
                }
            }

            foreach ($childsFromRequest as $childFromRequest) {
                if ($childFromRequest->getId() === null || $childFromRequest->getId() === 0) {
                    $docNo -= self::NAVISION_LINE_NO_SEPARATOR;
                    $childFromRequest = $this->initNewChildETDLine($etd, $etdLine, $childFromRequest, $docNo, $user);
                    $docNoCreated[$childFromRequest->getNavisionDocNo()] = $docNo;
                    $etd->addEtdLine($childFromRequest);
                    $this->_entityManager->persist($childFromRequest);
                    $this->_entityManager->persist($this->createETDLineHistory($childFromRequest));
                    $this->_entityManager->persist($this->createETDLineTagHistory($childFromRequest->getEtdLineTag()));
                    $this->addPartialMessageAsConversationMessage($childFromRequest, $user, $partialMessage);
                }
            }
            $this->updateETDLineStatusFromETDLineRequest($etdLine, $etdLineFromRequest, $user);
            $this->addPartialMessageAsConversationMessage($etdLine, $user, $partialMessage);
        }
        $etd = $this->updateETDStatus($etd);
        $etd = $this->_etdService->updateETDCounts($etd);
        $this->_conversationService->addETDConversationMessage($etd, $user, $contentMessage, $counters);
        return $etd;
    }

    #region ETDLineChild

    public function isETDLineUpdated(ETDLine $etdLineBefore, ETDLine $etdLineAfter): bool
    {
        return (int)$etdLineBefore->getOutstandingQtyConfirmed() !== (int)$etdLineAfter->getOutstandingQtyConfirmed() ||
            $this->isETDDateConfirmedUpdated($etdLineBefore, $etdLineAfter) ||
            $etdLineBefore->getShipByConfirmed() !== $etdLineAfter->getShipByConfirmed();
    }

    #endregion

    #region ETDLine

    private function isETDDateConfirmedUpdated(ETDLine $etdLine, ETDLine $etdLineFromRequest)
    {
        if ($etdLine->getEtdDateConfirmed() !== null && $etdLineFromRequest->getEtdDateConfirmed() !== null) {
            if ($etdLine->getEtdDateConfirmed()->getTimestamp() !== $etdLineFromRequest->getEtdDateConfirmed()->getTimestamp()) {
                return true;
            }
        } else if (($etdLine->getEtdDateConfirmed() !== null && $etdLineFromRequest->getEtdDateConfirmed() === null) ||
            ($etdLine->getEtdDateConfirmed() === null && $etdLineFromRequest->getEtdDateConfirmed() !== null)) {
            return true;
        }
        return false;
    }

    private function updateETDLineFromETDLineRequest(ETDLine $etdLine, ETDLine $etdLineFromRequest): ETDLine
    {
        $etdLineRequestQtyFormatted = number_format($etdLineFromRequest->getOutstandingQtyConfirmed(), 20, '.', '');
        if ($etdLine->getOutstandingQtyConfirmed() !== $etdLineRequestQtyFormatted) {
            $etdLine->setOutstandingQtyConfirmed($etdLineRequestQtyFormatted);
        }

        if ($this->isETDDateConfirmedUpdated($etdLine, $etdLineFromRequest)) {
            $etdLine->setEtdDateConfirmed($etdLineFromRequest->getEtdDateConfirmed());
        }

        if ($etdLine->getShipByConfirmed() !== $etdLineFromRequest->getShipByConfirmed()) {
            $etdLine->setShipByConfirmed($etdLineFromRequest->getShipByConfirmed());
        }

        /** @var ETDLineTag $etdLineTag */
        $etdLineTag = $etdLine->getEtdLineTag();
        $this->updateETDLineTagFromETDLineRequest($etdLineTag, $etdLineFromRequest);
        return $etdLine;
    }

    private function updateETDLineTagFromETDLineRequest(ETDLineTag $etdLineTag, ETDLine $etdLineFromRequest): ETDLineTag
    {
        $etdLineTag->setCompleted($etdLineFromRequest->getEtdLineTag()->getCompleted());
        $etdLineTag->setEtdChanged($etdLineFromRequest->getEtdLineTag()->getEtdChanged());
        $etdLineTag->setQtyChanged($etdLineFromRequest->getEtdLineTag()->getQtyChanged());
        $etdLineTag->setShipByChanged($etdLineFromRequest->getEtdLineTag()->getShipByChanged());
        $etdLineTag->setClosed($etdLineFromRequest->getEtdLineTag()->getClosed());
        $etdLineTag->setPartial($etdLineFromRequest->getEtdLineTag()->getPartial());
        return $etdLineTag;
    }

    private function createETDLineHistory(ETDLine $entity): ETDLineHistory
    {
        $history = new ETDLineHistory();
        $history->setStatus($entity->getStatus());
        $history->setEtdDate($entity->getEtdDateConfirmed());
        $history->setExportedToNavision(false);
        $history->setOutstandingQty($entity->getOutstandingQtyConfirmed());
        $history->setShipBy($entity->getShipByConfirmed());
        $history->setEtdLine($entity);
        return $history;
    }

    private function createETDLineTagHistory(ETDLineTag $entity): ETDLineTagHistory
    {
        $history = new ETDLineTagHistory();
        $history->setCompleted($entity->getCompleted());
        $history->setPartial($entity->getPartial());
        $history->setClosed($entity->getClosed());
        $history->setShipByChanged($entity->getShipByChanged());
        $history->setQtyChanged($entity->getQtyChanged());
        $history->setEtdChanged($entity->getEtdChanged());
        $history->setEtdLineTag($entity);
        return $history;
    }

    private function updateETDLineStatusFromETDLineRequest(ETDLine $etdLine, ETDLine $etdLineFromRequest, User $user): ETDLine
    {
        if ($user->getType() === UserType::VENDOR) {
            if ($etdLine->getStatus() === ETDLineStatusType::INITIAL || $etdLine->getStatus() === ETDLineStatusType::REJECTED) {
                $etdLine->setStatus(ETDLineStatusType::WAITING_FOR_APPROVAL);
            }
        } else if ($user->getType() === UserType::PURCHASER) {
            $etdLine->setStatus($etdLineFromRequest->getStatus());
        }
        return $etdLine;
    }

    private function addPartialMessageAsConversationMessage(ETDLine $etdLine, User $user, ?PartialMessage $partialMessage): void
    {
        if ($partialMessage && !StringTools::is_null_or_empty($partialMessage->getMessage())) {
            $this->_conversationService->addETDLineConversationMessage($etdLine, $user, $partialMessage->getMessage());
        }
    }

    private function initNewChildETDLine(ETD $etd, ETDLine $etdLine, ETDLine $etdLineToCreate, int $docNo, User $user): ETDLine
    {
        $etdLineToCreate->setEtd($etd);
        $etdLineToCreate->setParentETDLine($etdLine);
        $etdLineToCreate->setOriginalLineOutstandingQty($etdLine->getOutstandingQty());
        $etdLineToCreate->setStatus($etdLine->getStatus());
        $etdLineToCreate->setNavisionLineNo($docNo);

        switch ($user->getType()) {
            case UserType::PURCHASER:
                $etdLineToCreate->setStatus(ETDLineStatusType::INITIAL);
                break;
            case UserType::VENDOR:
                $etdLineToCreate->setStatus(ETDLineStatusType::WAITING_FOR_APPROVAL);
                $etdLineToCreate->setOrderType($etdLine->getOrderType());
                $etdLineToCreate->setStoreCode($etdLine->getStoreCode());
                $etdLineToCreate->setAvailableQty($etdLine->getAvailableQty());
                $etdLineToCreate->setYourReference($etdLine->getYourReference());
                $etdLineToCreate->setComments($etdLine->getComments());
                break;
            default:
                throw new UnexpectedValueException('This userType is not handled');
        }

        $etdLineTag = new ETDLineTag();
        $etdLineTag->setETDLine($etdLineToCreate);
        $etdLineTag = $this->updateETDLineTagFromETDLineRequest($etdLineTag, $etdLineToCreate);
        $etdLineToCreate->setEtdLineTag($etdLineTag);
        return $etdLineToCreate;
    }

    #endregion

    #region ETDLineTag

    private function updateETDStatus(ETD $etd, $etdLines = null): ETD
    {
        $areAllETDLinesApproved = true;
        if ($etdLines === null) {
            $etdLines = $etd->getEtdLines();
        }

        foreach ($etdLines as $etdLine) {
            if ($etdLine->getStatus() !== ETDLineStatusType::APPROVED && $etdLine->getStatus() !== ETDLineStatusType::REJECTED) {
                $areAllETDLinesApproved = false;
                break;
            }
        }

        if ($areAllETDLinesApproved) {
            $etd->setStatus(ETDStatusType::CLOSED);
            $etd->setClosed(true);
        }

        switch ($etd->getStatus()) {
            case ETDStatusType::CLOSED:
                break;
            case ETDStatusType::WAITING_PURCHASER:
                $etd->setStatus(ETDStatusType::WAITING_VENDOR);
                break;
            case ETDStatusType::WAITING_VENDOR:
                $etd->setStatus(ETDStatusType::WAITING_PURCHASER);
                break;
            default:
                throw new UnexpectedValueException('This status is not handled');
        }

        return $etd;
    }

    public function getConversationCount(int $etdId): ?array
    {
        return $this->_etdLineRepository->getConversationCount($etdId);
    }
    #endregion

    #region ETD

    public function findOneBy(array $criteria): ?ETDLine
    {
        return $this->_etdLineRepository->findOneBy($criteria);
    }
    #endregion

    #region Conversation

    public function addConversationMessage(int $etdLineId, User $user, string $contentMessage): bool
    {
        $etdLine = $this->_etdLineRepository->findOneBy(['id' => $etdLineId]);
        if ($contentMessage !== null && $contentMessage !== '' && $etdLine !== null) {
            return $this->_conversationService->addETDLineConversationMessage($etdLine, $user, $contentMessage);
        }
        return false;
    }

    #endregion
}