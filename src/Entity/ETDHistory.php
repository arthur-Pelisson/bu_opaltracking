<?php

namespace App\Entity;

use App\Repository\ETDHistoryRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ETDHistoryRepository::class)
 * @ORM\Table(name="etd_history")
 */
class ETDHistory
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="boolean")
     */
    private $closed;

    /**
     * @ORM\Column(type="etdstatustype")
     */
    private $status;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateAdd;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $userId;

    /**
     * @ORM\ManyToOne(targetEntity=ETD::class, inversedBy="etdHistories")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $etd;

    public function __construct()
    {
        $_date = new DateTime('now');
        $this->dateAdd = $_date;
    }

    public function getId(): ?int
    {
        return $this->id;
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


    public function getDateAdd(): ?\DateTimeInterface
    {
        return $this->dateAdd;
    }

    public function setDateAdd(\DateTimeInterface $dateAdd): self
    {
        $this->dateAdd = $dateAdd;

        return $this;
    }

    public function getUserId(): ?User
    {
        return $this->userId;
    }

    public function setUserId(?User $userId): self
    {
        $this->userId = $userId;

        return $this;
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
}
