<?php

namespace App\Entity;

use App\Repository\ETDLineTagHistoryRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ETDLineTagHistoryRepository::class)
 */
class ETDLineTagHistory
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=ETDLineTag::class, inversedBy="etdLineTagHistories")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $etdLineTag;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $completed;
    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $etdChanged;
    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $shipByChanged;
    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $qtyChanged;
    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $partial;
    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $closed;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateAdd;

    public function __construct()
    {
        $_date = new DateTime('now');
        $this->dateAdd = $_date;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEtdLineTag(): ?ETDLineTag
    {
        return $this->etdLineTag;
    }

    public function setEtdLineTag(?ETDLineTag $etdLineTag): self
    {
        $this->etdLineTag = $etdLineTag;

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

    public function getDateAdd(): ?\DateTimeInterface
    {
        return $this->dateAdd;
    }

    public function setDateAdd(\DateTimeInterface $dateAdd): self
    {
        $this->dateAdd = $dateAdd;

        return $this;
    }
}
