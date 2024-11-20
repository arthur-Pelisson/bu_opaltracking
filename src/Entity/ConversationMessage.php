<?php

namespace App\Entity;

use App\Repository\ConversationMessageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=ConversationMessageRepository::class)
 */
class ConversationMessage
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Conversation::class, inversedBy="conversationMessages", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $conversation;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"purchaser", "vendor", "messages"})
     */
    private $content;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"purchaser", "vendor", "messages"})
     */
    private $dateAdd;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"purchaser", "vendor", "messages"})
     */
    private $writeByUser;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"purchaser", "vendor", "messages"})
     */
    private $countValidateShipByChanged;
    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"purchaser", "vendor", "messages"})
     */
    private $countValidateETDDateChanged;
    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"purchaser", "vendor", "messages"})
     */
    private $countValidateQtyChanged;
    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"purchaser", "vendor", "messages"})
     */
    private $countApprovedChanged;
    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"purchaser", "vendor", "messages"})
     */
    private $countRejectedChanged;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConversation(): ?Conversation
    {
        return $this->conversation;
    }

    public function setConversation(?Conversation $conversation): self
    {
        $this->conversation = $conversation;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content): void
    {
        $this->content = $content;
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

    public function getWriteByUser(): ?User
    {
        return $this->writeByUser;
    }

    public function setWriteByUser(?User $writeByUser): self
    {
        $this->writeByUser = $writeByUser;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCountValidateShipByChanged()
    {
        return $this->countValidateShipByChanged;
    }

    /**
     * @param mixed $countValidateShipByChanged
     */
    public function setCountValidateShipByChanged($countValidateShipByChanged): void
    {
        $this->countValidateShipByChanged = $countValidateShipByChanged;
    }

    /**
     * @return mixed
     */
    public function getCountValidateETDDateChanged()
    {
        return $this->countValidateETDDateChanged;
    }

    /**
     * @param mixed $countValidateETDDateChanged
     */
    public function setCountValidateETDDateChanged($countValidateETDDateChanged): void
    {
        $this->countValidateETDDateChanged = $countValidateETDDateChanged;
    }

    /**
     * @return mixed
     */
    public function getCountValidateQtyChanged()
    {
        return $this->countValidateQtyChanged;
    }

    /**
     * @param mixed $countValidateQtyChanged
     */
    public function setCountValidateQtyChanged($countValidateQtyChanged): void
    {
        $this->countValidateQtyChanged = $countValidateQtyChanged;
    }

    /**
     * @return mixed
     */
    public function getCountApprovedChanged()
    {
        return $this->countApprovedChanged;
    }

    /**
     * @param mixed $countApprovedChanged
     */
    public function setCountApprovedChanged($countApprovedChanged): void
    {
        $this->countApprovedChanged = $countApprovedChanged;
    }

    /**
     * @return mixed
     */
    public function getCountRejectedChanged()
    {
        return $this->countRejectedChanged;
    }

    /**
     * @param mixed $countRejectedChanged
     */
    public function setCountRejectedChanged($countRejectedChanged): void
    {
        $this->countRejectedChanged = $countRejectedChanged;
    }
}
