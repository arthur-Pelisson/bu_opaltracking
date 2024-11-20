<?php

namespace App\Entity;

use App\Repository\VendorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * @ORM\Entity(repositoryClass=VendorRepository::class)
 */
class Vendor
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=20, unique=true)
     * @Groups({"purchaser", "vendor"})
     */
    private $no;

    /**
     * @ORM\Column(type="string", length=50)
     * @Groups({"purchaser", "vendor"})
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Groups({"purchaser", "vendor"})
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Groups({"purchaser", "vendor"})
     */
    private $address2;

    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     * @Groups({"purchaser", "vendor"})
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     * @Groups({"purchaser", "vendor"})
     */
    private $postCode;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Groups({"purchaser", "vendor"})
     */
    private $contact;

    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     * @Groups({"purchaser", "vendor"})
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     * @Groups({"purchaser", "vendor"})
     */
    private $telex;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     * @Groups({"purchaser", "vendor"})
     */
    private $territoryCode;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     * @Groups({"purchaser", "vendor"})
     */
    private $currencyCode;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     * @Groups({"purchaser", "vendor"})
     */
    private $countryRegionCode;

    /**
     * @ORM\Column(type="string", length=80)
     * @Groups({"purchaser", "vendor"})
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     * @Groups({"purchaser", "vendor"})
     */
    private $qc;

    /**
     * @ORM\Column(type="boolean")
     */
    private $enabled;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Groups({"purchaser", "vendor"})
     */
    private $startETDDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Groups({"purchaser", "vendor"})
     */
    private $endETDDate;

    /**
     * @ORM\OneToOne(targetEntity=User::class, mappedBy="vendor", cascade={"persist", "remove"})
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity=ETD::class, mappedBy="vendor", cascade={"persist", "remove"})
     */
    private $etds;


    public function __construct()
    {
        $this->etds = new ArrayCollection();
        $this->eTDs = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getNo()
    {
        return $this->no;
    }

    /**
     * @param mixed $no
     */
    public function setNo($no): void
    {
        $this->no = $no;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param mixed $address
     */
    public function setAddress($address): void
    {
        $this->address = $address;
    }

    /**
     * @return mixed
     */
    public function getAddress2()
    {
        return $this->address2;
    }

    /**
     * @param mixed $address2
     */
    public function setAddress2($address2): void
    {
        $this->address2 = $address2;
    }

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param mixed $city
     */
    public function setCity($city): void
    {
        $this->city = $city;
    }

    /**
     * @return mixed
     */
    public function getPostCode()
    {
        return $this->postCode;
    }

    /**
     * @param mixed $postCode
     */
    public function setPostCode($postCode): void
    {
        $this->postCode = $postCode;
    }

    /**
     * @return mixed
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @param mixed $contact
     */
    public function setContact($contact): void
    {
        $this->contact = $contact;
    }

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param mixed $phone
     */
    public function setPhone($phone): void
    {
        $this->phone = $phone;
    }

    /**
     * @return mixed
     */
    public function getTelex()
    {
        return $this->telex;
    }

    /**
     * @param mixed $telex
     */
    public function setTelex($telex): void
    {
        $this->telex = $telex;
    }

    /**
     * @return mixed
     */
    public function getTerritoryCode()
    {
        return $this->territoryCode;
    }

    /**
     * @param mixed $territoryCode
     */
    public function setTerritoryCode($territoryCode): void
    {
        $this->territoryCode = $territoryCode;
    }

    /**
     * @return mixed
     */
    public function getCurrencyCode()
    {
        return $this->currencyCode;
    }

    /**
     * @param mixed $currencyCode
     */
    public function setCurrencyCode($currencyCode): void
    {
        $this->currencyCode = $currencyCode;
    }

    /**
     * @return mixed
     */
    public function getCountryRegionCode()
    {
        return $this->countryRegionCode;
    }

    /**
     * @param mixed $countryRegionCode
     */
    public function setCountryRegionCode($countryRegionCode): void
    {
        $this->countryRegionCode = $countryRegionCode;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email): void
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getQc()
    {
        return $this->qc;
    }

    /**
     * @param mixed $qc
     */
    public function setQc($qc): void
    {
        $this->qc = $qc;
    }

    /**
     * @return mixed
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param mixed $enabled
     */
    public function setEnabled($enabled): void
    {
        $this->enabled = $enabled;
    }

    /**
     * @return mixed
     */
    public function getStartETDDate()
    {
        return $this->startETDDate;
    }

    /**
     * @param mixed $startETDDate
     */
    public function setStartETDDate($startETDDate): void
    {
        $this->startETDDate = $startETDDate;
    }

    /**
     * @return mixed
     */
    public function getEndETDDate()
    {
        return $this->endETDDate;
    }

    /**
     * @param mixed $endETDDate
     */
    public function setEndETDDate($endETDDate): void
    {
        $this->endETDDate = $endETDDate;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser($user): void
    {
        $this->user = $user;
    }

    /**
     * @return Collection|ETD[]
     */
    public function getEtds(): Collection
    {
        return $this->etds;
    }

    public function addEtd(ETD $etd): self
    {
        if (!$this->etds->contains($etd)) {
            $this->etds[] = $etd;
            $etd->setVendorNo($this);
        }

        return $this;
    }

    public function removeEtd(ETD $etd): self
    {
        if ($this->etds->removeElement($etd)) {
            // set the owning side to null (unless already changed)
            if ($etd->getVendorNo() === $this) {
                $etd->setVendorNo(null);
            }
        }

        return $this;
    }
}
