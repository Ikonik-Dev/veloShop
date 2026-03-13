<?php

namespace App\Entity;

use App\Repository\BikeVariantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BikeVariantRepository::class)]
#[ORM\Table(name: 'bike_variants')]
#[ORM\UniqueConstraint(name: 'unique_bike_variant', columns: ['bike_id', 'color', 'size'])]
class BikeVariant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Bike::class, inversedBy: 'variants')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Bike $bike = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    private string $color = '';

    #[ORM\Column(length: 10)]
    #[Assert\NotBlank]
    private string $size = ''; // XS, S, M, L, XL, XXL ou 48, 50, 52cm...

    #[ORM\ManyToOne(targetEntity: Motor::class, inversedBy: 'bikeVariants')]
    private ?Motor $motor = null; // null = non électrique

    #[ORM\Column(type: 'decimal', precision: 8, scale: 2)]
    #[Assert\PositiveOrZero]
    private string $basePrice = '0'; // Prix HT de base

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero]
    private ?int $weight = null; // en grammes

    #[ORM\Column(length: 20)]
    #[Assert\Choice(['new', 'refurbished', 'used'])]
    private string $bikeCondition = 'new';

    #[ORM\OneToMany(targetEntity: BikeSpecification::class, mappedBy: 'variant', cascade: ['all'], orphanRemoval: true)]
    private Collection $specifications;

    #[ORM\OneToMany(targetEntity: BikePrice::class, mappedBy: 'variant', cascade: ['all'], orphanRemoval: true)]
    private Collection $prices;

    #[ORM\OneToMany(targetEntity: Stock::class, mappedBy: 'variant', cascade: ['all'], orphanRemoval: true)]
    private Collection $stocks;

    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->specifications = new ArrayCollection();
        $this->prices = new ArrayCollection();
        $this->stocks = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBike(): ?Bike
    {
        return $this->bike;
    }

    public function setBike(?Bike $bike): self
    {
        $this->bike = $bike;
        return $this;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;
        return $this;
    }

    public function getSize(): string
    {
        return $this->size;
    }

    public function setSize(string $size): self
    {
        $this->size = $size;
        return $this;
    }

    public function getMotor(): ?Motor
    {
        return $this->motor;
    }

    public function setMotor(?Motor $motor): self
    {
        $this->motor = $motor;
        return $this;
    }

    public function isElectric(): bool
    {
        return $this->motor !== null;
    }

    public function getBasePrice(): string
    {
        return $this->basePrice;
    }

    public function setBasePrice(string $basePrice): self
    {
        $this->basePrice = $basePrice;
        return $this;
    }

    public function getWeight(): ?int
    {
        return $this->weight;
    }

    public function setWeight(?int $weight): self
    {
        $this->weight = $weight;
        return $this;
    }

    public function getBikeCondition(): string
    {
        return $this->bikeCondition;
    }

    public function setBikeCondition(string $bikeCondition): self
    {
        $this->bikeCondition = $bikeCondition;
        return $this;
    }

    /**
     * @return Collection<int, BikeSpecification>
     */
    public function getSpecifications(): Collection
    {
        return $this->specifications;
    }

    public function addSpecification(BikeSpecification $specification): self
    {
        if (!$this->specifications->contains($specification)) {
            $this->specifications->add($specification);
            $specification->setVariant($this);
        }
        return $this;
    }

    public function removeSpecification(BikeSpecification $specification): self
    {
        if ($this->specifications->removeElement($specification)) {
            if ($specification->getVariant() === $this) {
                $specification->setVariant(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, BikePrice>
     */
    public function getPrices(): Collection
    {
        return $this->prices;
    }

    public function addPrice(BikePrice $price): self
    {
        if (!$this->prices->contains($price)) {
            $this->prices->add($price);
            $price->setVariant($this);
        }
        return $this;
    }

    public function removePrice(BikePrice $price): self
    {
        if ($this->prices->removeElement($price)) {
            if ($price->getVariant() === $this) {
                $price->setVariant(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Stock>
     */
    public function getStocks(): Collection
    {
        return $this->stocks;
    }

    public function addStock(Stock $stock): self
    {
        if (!$this->stocks->contains($stock)) {
            $this->stocks->add($stock);
            $stock->setVariant($this);
        }
        return $this;
    }

    public function removeStock(Stock $stock): self
    {
        if ($this->stocks->removeElement($stock)) {
            if ($stock->getVariant() === $this) {
                $stock->setVariant(null);
            }
        }
        return $this;
    }

    public function getTotalStock(): int
    {
        return $this->stocks->reduce(function ($carry, Stock $stock) {
            return $carry + $stock->getQuantity();
        }, 0);
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

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}
