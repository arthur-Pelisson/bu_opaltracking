<?php

namespace App\Entity;

use App\Repository\ConversationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=ConversationRepository::class)
 */
class Conversation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=ETD::class, inversedBy="conversation")
     */
    private $etd;

    /**
     * @ORM\OneToOne(targetEntity=ETDLine::class, inversedBy="conversation")
     */
    private $etdLine;

    /**
     * @ORM\OneToMany(targetEntity=ConversationMessage::class, mappedBy="conversation", cascade={"persist", "remove"})
     * @Groups({"purchaser", "vendor"})
     */
    private $conversationMessages;

    public function __construct()
    {
        $this->conversationMessages = new ArrayCollection();
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

    public function getEtdLine(): ?ETDLine
    {
        return $this->etdLine;
    }

    public function setEtdLine(?ETDLine $etdLine): self
    {
        $this->etdLine = $etdLine;

        return $this;
    }

    /**
     * @return Collection|ConversationMessage[]
     */
    public function getConversationMessages(): Collection
    {
        return $this->conversationMessages;
    }

    public function addConversationMessage(ConversationMessage $conversationMessage): self
    {
        if (!$this->conversationMessages->contains($conversationMessage)) {
            $this->conversationMessages[] = $conversationMessage;
            $conversationMessage->setConversation($this);
        }

        return $this;
    }

    public function removeConversationMessage(ConversationMessage $conversationMessage): self
    {
        // set the owning side to null (unless already changed)
        if ($this->conversationMessages->removeElement($conversationMessage) && $conversationMessage->getConversation() === $this) {
            $conversationMessage->setConversation(null);
        }

        return $this;
    }
}
