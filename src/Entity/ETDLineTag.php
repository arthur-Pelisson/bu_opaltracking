<?php

namespace App\Entity;

use App\Repository\ETDLineTagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=ETDLineTagRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\EntityListeners({"App\EventListener\ETDLineTagListener"})
 */
class ETDLineTag
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"purchaser", "vendor"})
     */
    private $id;

    /**
     * @ORM\OneToOne (targetEntity=ETDLine::class, inversedBy="etdLineTag")
     * @ORM\JoinColumn(name="etdline_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $etdLine;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     * @Groups({"purchaser", "vendor"})
     */
    private $completed;
    /**
     * @ORM\Column(type="boolean", nullable=false)
     * @Groups({"purchaser", "vendor"})
     */
    private $etdChanged;
    /**
     * @ORM\Column(type="boolean", nullable=false)
     * @Groups({"purchaser", "vendor"})
     */
    private $shipByChanged;
    /**
     * @ORM\Column(type="boolean", nullable=false)
     * @Groups({"purchaser", "vendor"})
     */
    private $qtyChanged;
    /**
     * @ORM\Column(type="boolean", nullable=false)
     * @Groups({"purchaser", "vendor"})
     */
    private $partial;
    /**
     * @ORM\Column(type="boolean", nullable=false)
     * @Groups({"purchaser", "vendor"})
     */
    private $closed;
    /**
     * @ORM\OneToMany(targetEntity=ETDLineTagHistory::class, mappedBy="etdLineTag", orphanRemoval=true, cascade={"persist", "remove"})
     */
    private $etdLineTagHistories;

    public function __construct()
    {
        $this->etdLineTagHistories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getETDLine(): ETDLine
    {
        return $this->etdLine;
    }

    public function setETDLine(ETDLine $etdLine): self
    {
        $this->etdLine = $etdLine;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCompleted()
    {
        return $this->completed;
    }

    /**
     * @param mixed $completed
     */
    public function setCompleted($completed): void
    {
        $this->completed = $completed;
    }

    /**
     * @return mixed
     */
    public function getEtdChanged()
    {
        return $this->etdChanged;
    }

    /**
     * @param mixed $etdChanged
     */
    public function setEtdChanged($etdChanged): void
    {
        $this->etdChanged = $etdChanged;
    }

    /**
     * @return mixed
     */
    public function getShipByChanged()
    {
        return $this->shipByChanged;
    }

    /**
     * @param mixed $shipByChanged
     */
    public function setShipByChanged($shipByChanged): void
    {
        $this->shipByChanged = $shipByChanged;
    }

    /**
     * @return mixed
     */
    public function getQtyChanged()
    {
        return $this->qtyChanged;
    }

    /**
     * @param mixed $qtyChanged
     */
    public function setQtyChanged($qtyChanged): void
    {
        $this->qtyChanged = $qtyChanged;
    }

    /**
     * @return mixed
     */
    public function getPartial()
    {
        return $this->partial;
    }

    /**
     * @param mixed $partial
     */
    public function setPartial($partial): void
    {
        $this->partial = $partial;
    }

    /**
     * @return mixed
     */
    public function getClosed()
    {
        return $this->closed;
    }

    /**
     * @param mixed $closed
     */
    public function setClosed($closed): void
    {
        $this->closed = $closed;
    }

    /**
     * @return Collection|ETDLineTagHistory[]
     */
    public function getEtdLineTagHistories(): Collection
    {
        return $this->etdLineTagHistories;
    }

    public function addEtdLineTagHistory(ETDLineTagHistory $etdLineTagHistory): self
    {
        if (!$this->etdLineTagHistories->contains($etdLineTagHistory)) {
            $this->etdLineTagHistories[] = $etdLineTagHistory;
            $etdLineTagHistory->setEtdLineTag($this);
        }

        return $this;
    }

    public function removeEtdLineTagHistory(ETDLineTagHistory $etdLineTagHistory): self
    {
        if ($this->etdLineTagHistories->removeElement($etdLineTagHistory)) {
            // set the owning side to null (unless already changed)
            if ($etdLineTagHistory->getEtdLineTag() === $this) {
                $etdLineTagHistory->setEtdLineTag(null);
            }
        }

        return $this;
    }
}
