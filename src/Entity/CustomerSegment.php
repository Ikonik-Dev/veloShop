<?php

namespace App\Entity;

use App\Entity\BikePrice;
use App\Repository\CustomerSegmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CustomerSegmentRepository::class)]
#[ORM\Table(name: 'customer_segments')]
class CustomerSegment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    private string $name = '';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2)]
    #[Assert\Range(min: '0', max: '100')]
    private string $discountRate = '0'; // Pourcentage de réduction

    #[ORM\OneToMany(targetEntity: BikePrice::class, mappedBy: 'segment')]
    private Collection $bikePrices;

    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->bikePrices = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getDiscountRate(): string
    {
        return $this->discountRate;
    }

    public function setDiscountRate(string $discountRate): self
    {
        $this->discountRate = $discountRate;
        return $this;
    }

    /**
     * @return Collection<int, BikePrice>
     */
    public function getBikePrices(): Collection
    {
        return $this->bikePrices;
    }

    public function addBikePrice(BikePrice $bikePrice): self
    {
        if (!$this->bikePrices->contains($bikePrice)) {
            $this->bikePrices->add($bikePrice);
            $bikePrice->setSegment($this);
        }
        return $this;
    }

    public function removeBikePrice(BikePrice $bikePrice): self
    {
        if ($this->bikePrices->removeElement($bikePrice)) {
            if ($bikePrice->getSegment() === $this) {
                $bikePrice->setSegment(null);
            }
        }
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
