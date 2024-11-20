<?php

namespace App\Dto;

class UpdateETDLinesMessageCounters
{
    public ?int $countValidateShipByChanged;
    public ?int $countValidateETDDateChanged;
    public ?int $countValidateQtyChanged;
    public ?int $countApprovedChanged;
    public ?int $countRejectedChanged;

    /**
     * @return int|null
     */
    public function getCountValidateShipByChanged(): ?int
    {
        return $this->countValidateShipByChanged;
    }

    /**
     * @param int|null $countValidateShipByChanged
     */
    public function setCountValidateShipByChanged(?int $countValidateShipByChanged): void
    {
        $this->countValidateShipByChanged = $countValidateShipByChanged;
    }

    /**
     * @return int|null
     */
    public function getCountValidateETDDateChanged(): ?int
    {
        return $this->countValidateETDDateChanged;
    }

    /**
     * @param int|null $countValidateETDDateChanged
     */
    public function setCountValidateETDDateChanged(?int $countValidateETDDateChanged): void
    {
        $this->countValidateETDDateChanged = $countValidateETDDateChanged;
    }

    /**
     * @return int|null
     */
    public function getCountValidateQtyChanged(): ?int
    {
        return $this->countValidateQtyChanged;
    }

    /**
     * @param int|null $countValidateQtyChanged
     */
    public function setCountValidateQtyChanged(?int $countValidateQtyChanged): void
    {
        $this->countValidateQtyChanged = $countValidateQtyChanged;
    }

    /**
     * @return int|null
     */
    public function getCountApprovedChanged(): ?int
    {
        return $this->countApprovedChanged;
    }

    /**
     * @param int|null $countApprovedChanged
     */
    public function setCountApprovedChanged(?int $countApprovedChanged): void
    {
        $this->countApprovedChanged = $countApprovedChanged;
    }

    /**
     * @return int|null
     */
    public function getCountRejectedChanged(): ?int
    {
        return $this->countRejectedChanged;
    }

    /**
     * @param int|null $countRejectedChanged
     */
    public function setCountRejectedChanged(?int $countRejectedChanged): void
    {
        $this->countRejectedChanged = $countRejectedChanged;
    }
}