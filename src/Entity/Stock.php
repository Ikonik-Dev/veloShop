<?php

namespace App\Entity;

use App\Repository\StockRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: StockRepository::class)]
#[ORM\Table(name: 'stocks')]
#[ORM\UniqueConstraint(name: 'unique_variant_warehouse', columns: ['variant_id', 'warehouse'])]
class Stock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: BikeVariant::class, inversedBy: 'stocks')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?BikeVariant $variant = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    private string $warehouse = ''; // Entrepôt/Localisation

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    private int $quantity = 0;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero]
    private ?int $reorderLevel = null; // Seuil de réapprovisionnement

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $lastRestockDate;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->lastRestockDate = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVariant(): ?BikeVariant
    {
        return $this->variant;
    }

    public function setVariant(?BikeVariant $variant): self
    {
        $this->variant = $variant;
        return $this;
    }

    public function getWarehouse(): string
    {
        return $this->warehouse;
    }

    public function setWarehouse(string $warehouse): self
    {
        $this->warehouse = $warehouse;
        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function addQuantity(int $amount): self
    {
        $this->quantity += $amount;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function removeQuantity(int $amount): self
    {
        $this->quantity = max(0, $this->quantity - $amount);
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getReorderLevel(): ?int
    {
        return $this->reorderLevel;
    }

    public function setReorderLevel(?int $reorderLevel): self
    {
        $this->reorderLevel = $reorderLevel;
        return $this;
    }

    public function isLowStock(): bool
    {
        if ($this->reorderLevel === null) {
            return false;
        }
        return $this->quantity <= $this->reorderLevel;
    }

    public function getLastRestockDate(): \DateTimeImmutable
    {
        return $this->lastRestockDate;
    }

    public function setLastRestockDate(\DateTimeImmutable $lastRestockDate): self
    {
        $this->lastRestockDate = $lastRestockDate;
        return $this;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
