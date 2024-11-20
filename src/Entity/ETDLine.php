<?php

namespace App\Entity;

use App\Repository\ETDLineRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * @ORM\Entity(repositoryClass=ETDLineRepository::class)
 * @ORM\Table(name="etdline", uniqueConstraints={@ORM\UniqueConstraint(name="etdline_index", columns={"navision_doc_no", "navision_line_no"})});
 * @ORM\HasLifecycleCallbacks()
 * @ORM\EntityListeners({"App\EventListener\ETDLineListener"})
*/
class ETDLine
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"purchaser", "vendor"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=ETD::class, inversedBy="etdLines", fetch="EAGER")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $etd;

    /**
     * @ORM\Column(type="string", length=20)
     * @Groups({"purchaser", "vendor"})
     */
    private $piNo;

    /**
     * @ORM\Column(type="string", length=50)
     * @Groups({"purchaser", "vendor"})
     */
    private $itemReference;

    /**
     * @ORM\Column(type="decimal", precision=38, scale=20)
     * @Groups({"purchaser", "vendor"})
     */
    private $outstandingQty;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"purchaser", "vendor"})
     */
    private $deliveryDate;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"purchaser", "vendor"})
     */
    private $etdDate;

    /**
     * @ORM\Column(type="string", length=10)
     * @Groups({"purchaser", "vendor"})
     */
    private $shipBy;

    /**
     * @ORM\Column(type="decimal", precision=38, scale=20)
     * @Groups({"purchaser", "vendor"})
     */
    private $outstandingQtyConfirmed;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateAdd;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateUpdate;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"purchaser", "vendor"})
     */
    private $orderType;

    /**
     * @ORM\Column(type="string", length=50)
     * @Groups({"purchaser", "vendor"})
     */
    private $storeCode;

    /**
     * @ORM\Column(type="decimal", precision=38, scale=20, nullable=true)
     * @Groups({"purchaser", "vendor"})
     */
    private $availableQty;

    /**
     * @ORM\Column(type="decimal", precision=38, scale=20, nullable=true)
     * @Groups({"purchaser", "vendor"})
     */
    private $originalLineOutstandingQty;

    /**
     * @ORM\Column(type="string", length=35, nullable=true)
     * @Groups({"purchaser"})
     */
    private $yourReference;

    /**
     * @ORM\Column(type="etdlinestatustype")
     * @Groups({"purchaser", "vendor"})
     */
    private $status;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"purchaser", "vendor"})
     */
    private $etdDateConfirmed;

    /**
     * @ORM\Column(type="string", length=10)
     * @Groups({"purchaser", "vendor"})
     */
    private $shipByConfirmed;

    /**
     * @ORM\OneToMany(targetEntity=ETDLineHistory::class, mappedBy="etdLine", cascade={"persist", "remove"})
     */
    private $etdLinesHistories;

    /**
     * @ORM\OneToOne(targetEntity=Conversation::class, mappedBy="etdLine", cascade={"persist", "remove"})
     */
    private $conversation;

    /**
     * @ORM\ManyToOne(targetEntity=ETDLine::class, inversedBy="childETDLines")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Groups({"purchaser", "vendor"})
     */
    private $parentETDLine;

    /**
     * @ORM\OneToMany(targetEntity=ETDLine::class, mappedBy="parentETDLine", cascade={"remove"})
     * @Groups({"purchaser", "vendor"})
     */
    private $childETDLines;

    /**
     * @ORM\Column(type="string", length=20)
     * @Groups({"purchaser", "vendor"})
     */
    private $navisionDocNo;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"purchaser", "vendor"})
     */
    private $navisionLineNo;

    /**
     * @ORM\OneToOne(targetEntity=ETDLineTag::class, mappedBy="etdLine", orphanRemoval=true, cascade={"persist", "remove"})
     * @Groups({"purchaser", "vendor"})
     */
    private $etdLineTag;

    /**
     * @ORM\Column(type="boolean")
     */
    private $exportedToNavision;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"purchaser", "vendor"})
     */
    private $readOnly;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     * @Groups({"purchaser"})
     */
    private $comments;



    public function __construct()
    {
        $this->etdLinesHistories = new ArrayCollection();
        $this->childETDLines = new ArrayCollection();
        $_date = new DateTime('now');
        $this->dateAdd = $_date;
        $this->dateUpdate = $_date;
        $this->exportedToNavision = false;
    }


    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEtd(): ?ETD
    {
        return $this->etd;
    }

    public function setEtd(?ETD $etd): self
    {
        $this->etd = $etd;

        return $this;
    }

    public function getPiNo(): ?string
    {
        return $this->piNo;
    }

    public function setPiNo(string $piNo): self
    {
        $this->piNo = $piNo;

        return $this;
    }

    public function getItemReference(): ?string
    {
        return $this->itemReference;
    }

    public function setItemReference(string $itemReference): self
    {
        $this->itemReference = $itemReference;

        return $this;
    }


    public function getDeliveryDate(): ?DateTimeInterface
    {
        return $this->deliveryDate;
    }

    public function setDeliveryDate(DateTimeInterface $deliveryDate): self
    {
        $this->deliveryDate = $deliveryDate;

        return $this;
    }

    public function getEtdDate(): ?DateTimeInterface
    {
        return $this->etdDate;
    }

    public function setEtdDate(DateTimeInterface $etdDate): self
    {
        $this->etdDate = $etdDate;

        return $this;
    }

    public function getShipBy(): ?string
    {
        return $this->shipBy;
    }

    public function setShipBy(string $shipBy): self
    {
        $this->shipBy = $shipBy;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOutstandingQty()
    {
        return $this->outstandingQty;
    }

    /**
     * @param mixed $outstandingQty
     */
    public function setOutstandingQty($outstandingQty): void
    {
        $this->outstandingQty = $outstandingQty;
    }

    /**
     * @return mixed
     */
    public function getOutstandingQtyConfirmed()
    {
        return $this->outstandingQtyConfirmed;
    }

    /**
     * @param mixed $outstandingQtyConfirmed
     */
    public function setOutstandingQtyConfirmed($outstandingQtyConfirmed): void
    {
        $this->outstandingQtyConfirmed = $outstandingQtyConfirmed;
    }

    public function getDateAdd(): ?DateTimeInterface
    {
        return $this->dateAdd;
    }

    public function setDateAdd(DateTimeInterface $dateAdd): self
    {
        $this->dateAdd = $dateAdd;

        return $this;
    }

    public function getDateUpdate(): ?DateTimeInterface
    {
        return $this->dateUpdate;
    }

    public function setDateUpdate(DateTimeInterface $dateUpdate): self
    {
        $this->dateUpdate = $dateUpdate;

        return $this;
    }

    public function getOrderType(): int
    {
        return $this->orderType;
    }

    public function setOrderType(int $orderType): self
    {
        $this->orderType = $orderType;

        return $this;
    }

    public function getStoreCode(): ?string
    {
        return $this->storeCode;
    }

    public function setStoreCode(string $storeCode): self
    {
        $this->storeCode = $storeCode;

        return $this;
    }

    public function getYourReference(): ?string
    {
        return $this->yourReference;
    }

    public function setYourReference(?string $yourReference): self
    {
        $this->yourReference = $yourReference;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getEtdDateConfirmed(): ?\DateTimeInterface
    {
        return $this->etdDateConfirmed;
    }

    public function setEtdDateConfirmed(?\DateTimeInterface $etdDateConfirmed): self
    {
        $this->etdDateConfirmed = $etdDateConfirmed;

        return $this;
    }

    public function getShipByConfirmed(): ?string
    {
        return $this->shipByConfirmed;
    }

    public function setShipByConfirmed(string $shipByConfirmed): self
    {
        $this->shipByConfirmed = $shipByConfirmed;

        return $this;
    }

    /**
     * @return Collection|ETDLineHistory[]
     */
    public function getEtdLinesHistories(): Collection
    {
        return $this->etdLinesHistories;
    }

    public function addEtdLinesHistory(ETDLineHistory $etdLinesHistory): self
    {
        if (!$this->etdLinesHistories->contains($etdLinesHistory)) {
            $this->etdLinesHistories[] = $etdLinesHistory;
            $etdLinesHistory->setEtdLine($this);
        }

        return $this;
    }

    public function removeEtdLinesHistory(ETDLineHistory $etdLinesHistory): self
    {
        if ($this->etdLinesHistories->removeElement($etdLinesHistory)) {
            // set the owning side to null (unless already changed)
            if ($etdLinesHistory->getEtdLine() === $this) {
                $etdLinesHistory->setEtdLine(null);
            }
        }

        return $this;
    }

    public function getConversation(): ?Conversation
    {
        return $this->conversation;
    }

    public function setConversation(?Conversation $conversation): self
    {
        // unset the owning side of the relation if necessary
        if ($conversation === null && $this->conversation !== null) {
            $this->conversation->setETDLine(null);
        }

        // set the owning side of the relation if necessary
        if ($conversation !== null && $conversation->getETDLine() !== $this) {
            $conversation->setETDLine($this);
        }

        $this->conversation = $conversation;

        return $this;
    }

    public function getParentETDLine(): ?self
    {
        return $this->parentETDLine;
    }

    public function setParentETDLine(?self $parentETDLine): self
    {
        $this->parentETDLine = $parentETDLine;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getChildETDLines(): Collection
    {
        return $this->childETDLines;
    }

    public function addChildETDLine(self $childETDLine): self
    {
        if (!$this->childETDLines->contains($childETDLine)) {
            $this->childETDLines[] = $childETDLine;
            $childETDLine->setParentETDLine($this);
        }

        return $this;
    }

    public function removeChildETDLine(self $childETDLine): self
    {
        if ($this->childETDLines->removeElement($childETDLine)) {
            // set the owning side to null (unless already changed)
            if ($childETDLine->getParentETDLine() === $this) {
                $childETDLine->setParentETDLine(null);
            }
        }

        return $this;
    }

    public function getNavisionDocNo(): ?string
    {
        return $this->navisionDocNo;
    }

    public function setNavisionDocNo(string $navisionDocNo): self
    {
        $this->navisionDocNo = $navisionDocNo;

        return $this;
    }

    public function getNavisionLineNo(): ?int
    {
        return $this->navisionLineNo;
    }

    public function setNavisionLineNo(int $navisionLineNo): self
    {
        $this->navisionLineNo = $navisionLineNo;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEtdLineTag()
    {
        return $this->etdLineTag;
    }

    /**
     * @param mixed $etdLineTag
     */
    public function setEtdLineTag($etdLineTag): void
    {
        $this->etdLineTag = $etdLineTag;
    }


    public function getComments(): ?string
    {
        return $this->comments;
    }

    public function setComments(?string $comments): self
    {
        $this->comments = $comments;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getReadOnly()
    {
        return $this->readOnly;
    }

    /**
     * @param mixed $readOnly
     */
    public function setReadOnly($readOnly): void
    {
        $this->readOnly = $readOnly;
    }

    /**
     * @return false
     */
    public function getExportedToNavision(): bool
    {
        return $this->exportedToNavision;
    }

    /**
     * @param false $exportedToNavision
     */
    public function setExportedToNavision(bool $exportedToNavision): void
    {
        $this->exportedToNavision = $exportedToNavision;
    }

    /**
     * @return mixed
     */
    public function getOriginalLineOutstandingQty()
    {
        return $this->originalLineOutstandingQty;
    }

    /**
     * @param mixed $originalLineOutstandingQty
     */
    public function setOriginalLineOutstandingQty($originalLineOutstandingQty): void
    {
        $this->originalLineOutstandingQty = $originalLineOutstandingQty;
    }

    /**
     * @return mixed
     */
    public function getAvailableQty()
    {
        return $this->availableQty;
    }

    /**
     * @param mixed $availableQty
     */
    public function setAvailableQty($availableQty): void
    {
        $this->availableQty = $availableQty;
    }
}
