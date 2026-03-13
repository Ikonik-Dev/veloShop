<?php

namespace App\Entity;

use App\Entity\Package;
use App\Repository\PackageItemRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PackageItemRepository::class)]
#[ORM\Table(name: 'package_items')]
class PackageItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Package::class, inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Package $package = null;

    #[ORM\ManyToOne(targetEntity: BikeVariant::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?BikeVariant $variant = null;

    #[ORM\Column]
    #[Assert\Positive]
    private int $quantity = 1;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    #[Assert\PositiveOrZero]
    private ?string $priceOverride = null; // Prix spécifique au package (sinon prix standard)

    #[ORM\Column]
    private int $position = 0; // Ordre d'affichage

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPackage(): ?Package
    {
        return $this->package;
    }

    public function setPackage(?Package $package): self
    {
        $this->package = $package;
        return $this;
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

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function getPriceOverride(): ?string
    {
        return $this->priceOverride;
    }

    public function setPriceOverride(?string $priceOverride): self
    {
        $this->priceOverride = $priceOverride;
        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;
        return $this;
    }
}
