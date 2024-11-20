<?php

namespace App\Entity;

use App\Repository\ETDLineHistoryRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ETDLineHistoryRepository::class)
 * @ORM\Table(name="etdline_history")
 */
class ETDLineHistory
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="etdlinestatustype", length=255)
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity=ETDLine::class, inversedBy="etdLinesHistories")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $etdLine;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $shipBy;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $etdDate;

    /**
     * @ORM\Column(type="decimal", precision=38, scale=20)
     */
    private $outstandingQty;

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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getEtdLine(): ?ETDLine
    {
        return $this->etdLine;
    }

    public function setEtdLine(?ETDLine $etdLine): self
    {
        $this->etdLine = $etdLine;

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

    public function getEtdDate(): ?\DateTimeInterface
    {
        return $this->etdDate;
    }

    public function setEtdDate(?\DateTimeInterface $etdDate): self
    {
        $this->etdDate = $etdDate;

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

    public function getDateAdd(): ?\DateTimeInterface
    {
        return $this->dateAdd;
    }

    public function setDateAdd(\DateTimeInterface $dateAdd): self
    {
        $this->dateAdd = $dateAdd;

        return $this;
    }

    public function getExportedToNavision(): ?bool
    {
        return $this->exportedToNavision;
    }

    public function setExportedToNavision(bool $exportedToNavision): self
    {
        $this->exportedToNavision = $exportedToNavision;

        return $this;
    }
}
