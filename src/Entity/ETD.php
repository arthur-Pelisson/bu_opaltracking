<?php

namespace App\Entity;

use App\Repository\ETDRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=ETDRepository::class)
 * @ORM\Table(name="etd", uniqueConstraints={@ORM\UniqueConstraint(name="etd_index", columns={"vendor_id", "etd_date"})})
 * @ORM\HasLifecycleCallbacks()
 * @ORM\EntityListeners({"App\EventListener\ETDListener"})
 */
class ETD
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"purchaser", "vendor"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Vendor::class, inversedBy="etds", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"purchaser", "vendor"})
     */
    private $vendor;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"purchaser", "vendor"})
     */
    private $closed;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"purchaser", "vendor"})
     */
    private $dateAdd;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"purchaser", "vendor"})
     */
    private $dateUpdate;

    /**
     * @ORM\Column(type="etdstatustype")
     * @Groups({"purchaser", "vendor"})
     */
    private $status;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"purchaser", "vendor"})
     */
    private $etdDate;

    /**
     * @ORM\OneToMany(targetEntity=ETDHistory::class, mappedBy="etd", cascade={"persist", "remove"})
     */
    private $etdHistories;

    /**
     * @ORM\OneToMany(targetEntity=ETDLine::class, mappedBy="etd", cascade={"persist", "remove"})
     */
    private $etdLines;

    /**
     * @ORM\OneToOne(targetEntity=Conversation::class, mappedBy="etd", cascade={"persist", "remove"})
     */
    private $conversation;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"purchaser", "vendor"})
     */
    private $totalETDLinesCount;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"purchaser", "vendor"})
     */
    private $notValidatedETDLinesCount;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"purchaser", "vendor"})
     */
    private $etdChangedETDLinesCount;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"purchaser", "vendor"})
     */
    private $qtyChangedETDLinesCount;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"purchaser", "vendor"})
     */
    private $shipByChangedETDLinesCount;


    public function __construct()
    {
        $this->etdHistories = new ArrayCollection();
        $this->etdLines = new ArrayCollection();
        $_date = new DateTime('now');
        $this->dateAdd = $_date;
        $this->dateUpdate = $_date;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVendor(): Vendor
    {
        return $this->vendor;
    }

    public function setVendor(Vendor $vendor): self
    {
        $this->vendor = $vendor;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status): void
    {
        $this->status = $status;
    }

    public function getClosed(): ?bool
    {
        return $this->closed;
    }

    public function setClosed(bool $closed): self
    {
        $this->closed = $closed;

        return $this;
    }

    public function getDateAdd(): ?\DateTimeInterface
    {
        return $this->dateAdd;
    }

    public function setDateAdd(\DateTimeInterface $dateAdd): self
    {
        $this->dateAdd = $dateAdd;

        return $this;
    }

    public function getDateUpdate(): ?\DateTimeInterface
    {
        return $this->dateUpdate;
    }

    public function setDateUpdate(\DateTimeInterface $dateUpdate): self
    {
        $this->dateUpdate = $dateUpdate;

        return $this;
    }

    public function getEtdDate(): DateTimeInterface
    {
        return $this->etdDate;
    }

    public function setEtdDate(DateTimeInterface $etdDate): self
    {
        $this->etdDate = $etdDate;

        return $this;
    }

    /**
     * @return Collection|ETDHistory[]
     */
    public function getEtdHistories(): Collection
    {
        return $this->etdHistories;
    }

    public function addEtdHistory(ETDHistory $etdHistory): self
    {
        if (!$this->etdHistories->contains($etdHistory)) {
            $this->etdHistories[] = $etdHistory;
            $etdHistory->setEtd($this);
        }

        return $this;
    }

    public function removeEtdHistory(ETDHistory $etdHistory): self
    {
        if ($this->etdHistories->removeElement($etdHistory)) {
            // set the owning side to null (unless already changed)
            if ($etdHistory->getEtd() === $this) {
                $etdHistory->setEtd(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ETDLine[]
     */
    public function getEtdLines(): Collection
    {
        return $this->etdLines;
    }

    public function addEtdLine(ETDLine $etdLine): self
    {
        if (!$this->etdLines->contains($etdLine)) {
            $this->etdLines[] = $etdLine;
            $etdLine->setEtd($this);
        }

        return $this;
    }

    public function removeEtdLine(ETDLine $etdLine): self
    {
        if ($this->etdLines->removeElement($etdLine)) {
            // set the owning side to null (unless already changed)
            if ($etdLine->getEtd() === $this) {
                $etdLine->setEtd(null);
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
            $this->conversation->setEtd(null);
        }

        // set the owning side of the relation if necessary
        if ($conversation !== null && $conversation->getEtd() !== $this) {
            $conversation->setEtd($this);
        }

        $this->conversation = $conversation;

        return $this;
    }

    public function getTotalETDLinesCount(): ?int
    {
        return $this->totalETDLinesCount;
    }

    public function setTotalETDLinesCount(int $totalETDLinesCount): self
    {
        $this->totalETDLinesCount = $totalETDLinesCount;

        return $this;
    }

    public function getNotValidatedETDLinesCount(): ?int
    {
        return $this->notValidatedETDLinesCount;
    }

    public function setNotValidatedETDLinesCount(int $notValidatedETDLinesCount): self
    {
        $this->notValidatedETDLinesCount = $notValidatedETDLinesCount;

        return $this;
    }

    public function getEtdChangedETDLinesCount(): ?int
    {
        return $this->etdChangedETDLinesCount;
    }

    public function setEtdChangedETDLinesCount(int $etdChangedETDLinesCount): self
    {
        $this->etdChangedETDLinesCount = $etdChangedETDLinesCount;

        return $this;
    }

    public function getQtyChangedETDLinesCount(): ?int
    {
        return $this->qtyChangedETDLinesCount;
    }

    public function setQtyChangedETDLinesCount(int $qtyChangedETDLinesCount): self
    {
        $this->qtyChangedETDLinesCount = $qtyChangedETDLinesCount;

        return $this;
    }

    public function getShipByChangedETDLinesCount(): ?int
    {
        return $this->shipByChangedETDLinesCount;
    }

    public function setShipByChangedETDLinesCount(int $shipByChangedETDLinesCount): self
    {
        $this->shipByChangedETDLinesCount = $shipByChangedETDLinesCount;

        return $this;
    }
}
