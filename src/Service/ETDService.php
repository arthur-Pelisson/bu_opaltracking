<?php


namespace App\Service;


use App\Entity\ETD;
use App\Entity\ETDLineTag;
use App\Entity\User;
use App\Repository\ETDRepository;
use App\Type\ETDLineStatusType;
use App\Type\ETDLineTagType;
use App\Type\ETDStatusType;
use App\Type\UserType;
use Doctrine\ORM\Query;
use http\Exception\InvalidArgumentException;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use UnexpectedValueException;

class ETDService
{
    private ETDRepository $_etdRepository;
    private ConversationService $_conversationService;

    public function __construct(ETDRepository $etdRepository, ConversationService $conversationService)
    {
        $this->_etdRepository = $etdRepository;
        $this->_conversationService = $conversationService;
    }


    public function getLastETDsUpdated(User $user): array
    {
        switch ($user->getType()) {
            case UserType::PURCHASER:
                return $this->_etdRepository->getLastETDsUpdated($user->getType());
            case UserType::VENDOR:
                return $this->_etdRepository->getLastETDsUpdated($user->getType(), $user->getVendor()->getNo());
            default:
                throw new UnexpectedValueException('This userType is not handled');
        }
    }

    public function updateETDsCounts(array $etdIds): array
    {
        $updatedETDIds = [];
        foreach ($etdIds as $etdId) {
            /** @var ETD $etd */
            $etd = $this->_etdRepository->findOneBy(['id' => $etdId]);
            $etdUpdated = $this->updateETDCounts($etd);
            if ($etdUpdated !== null) {
                $updatedETDIds[] = $etd->getId();
            }
        }
        return $updatedETDIds;
    }

    public function canUserUpdateETD(ETD $etd, User $user): bool
    {
        if ($etd->getStatus() === ETDStatusType::CLOSED) {
            return false;
        }

        if ($user->getType() === UserType::VENDOR && $etd->getStatus() === ETDStatusType::WAITING_VENDOR) {
            return (($user->getVendor()->getStartETDDate() <= $etd->getEtdDate() || $user->getVendor()->getStartETDDate() === null) &&
                ($user->getVendor()->getEndETDDate() >= $etd->getEtdDate() || $user->getVendor()->getEndETDDate() === null));
        }
        if ($user->getType() === UserType::PURCHASER && $etd->getStatus() === ETDStatusType::WAITING_PURCHASER) {
            return true;
        }

        return false;
    }

    public function updateETDCounts(ETD $etd, $etdLines = null)
    {
        if ($etd === null) {
            return null;
        }
        if($etdLines === null) {
            $etdLines = $etd->getEtdLines();
        }

        $etd->setEtdChangedETDLinesCount(0);
        $etd->setShipByChangedETDLinesCount(0);
        $etd->setQtyChangedETDLinesCount(0);
        $etd->setTotalETDLinesCount(0);
        $etd->setNotValidatedETDLinesCount(0);

        foreach ($etdLines as $etdLine) {
            if ($etdLine->getStatus() !== ETDLineStatusType::APPROVED) {
                $etd->setNotValidatedETDLinesCount($etd->getNotValidatedETDLinesCount() + 1);
            }
            /** @var ETDLineTag $etdLineTag */
            $etdLineTag = $etdLine->getEtdLineTag();

            if ($etdLineTag->getEtdChanged()) {
                $etd->setEtdChangedETDLinesCount($etd->getEtdChangedETDLinesCount() + 1);
            }
            if ($etdLineTag->getShipByChanged()) {
                $etd->setShipByChangedETDLinesCount($etd->getShipByChangedETDLinesCount() + 1);
            }
            if ($etdLineTag->getQtyChanged() || $etdLineTag->getPartial()) {
                $etd->setQtyChangedETDLinesCount($etd->getQtyChangedETDLinesCount() + 1);
            }
        }
        $etd->setTotalETDLinesCount(count($etdLines));

        return $etd;
    }

    public function closeETD(int $etdId, User $user, string $contentMessage): bool
    {
        if ($user->getType() !== UserType::PURCHASER) {
            return false;
        }

        $etd = $this->_etdRepository->findETDById($etdId);

        if ($etd === null) {
            return false;
        }

        $etd->setClosed(true);
        $etd->setStatus(ETDStatusType::CLOSED);

        if ($contentMessage !== null && $contentMessage !== '') {
            $this->_conversationService->addETDConversationMessage($etd, $user, $contentMessage);
        }

        return true;
    }

    public function openETD(int $etdId, User $user, string $contentMessage): bool
    {
        if ($user->getType() !== UserType::PURCHASER) {
            return false;
        }

        $etd = $this->_etdRepository->findETDById($etdId);

        if ($etd === null) {
            return false;
        }

        $etd->setClosed(false);
        $etd->setStatus(ETDStatusType::WAITING_PURCHASER);

        if ($contentMessage !== null && $contentMessage !== '') {
            $this->_conversationService->addETDConversationMessage($etd, $user, $contentMessage);
        }

        return true;
    }

    public function addConversationMessage(int $etdId, User $user, string $contentMessage): bool
    {
        $etd = $this->_etdRepository->findETDById($etdId);
        if ($contentMessage !== null && $etd !== null) {
            return $this->_conversationService->addETDConversationMessage($etd, $user, $contentMessage);
        }
        return false;
    }

    public function getETDsPagination(PaginatorInterface $paginator, int $page, User $user, bool $isArchivesETDs = false, bool $isOnlyActive = false): PaginationInterface
    {
        $queryETDs = $this->getETDsQuery($user, $isArchivesETDs, $isOnlyActive);
        return $paginator->paginate(
            $queryETDs,
            $page,
            40
        );
    }

    public function getETDsQuery(User $user, bool $isArchivesETDs, bool $isOnlyActive): Query
    {
        return $this->_etdRepository->queryFindETDs($user, $isArchivesETDs, $isOnlyActive);
    }

    public function findETDById(int $id): ?ETD
    {
        return $this->_etdRepository->findETDById($id);
    }

    public function findETDWithCounts(int $id): ?array
    {
        return $this->_etdRepository->findETDWithCounts($id);
    }
}