<?php

namespace App\Dto;

use DateTime;

class ETDFile
{
    public ?string $name;
    public ?DateTime $modificationDate;

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return DateTime|null
     */
    public function getModificationDate(): ?DateTime
    {
        return $this->modificationDate;
    }

    /**
     * @param DateTime|null $modificationDate
     */
    public function setModificationDate(?DateTime $modificationDate): void
    {
        $this->modificationDate = $modificationDate;
    }
}