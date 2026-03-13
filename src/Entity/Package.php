<?php

namespace App\Entity;

use App\Entity\PackageItem;
use App\Repository\PackageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PackageRepository::class)]
#[ORM\Table(name: 'packages')]
#[ORM\Index(columns: ['slug'])]
class Package
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    private string $name = '';

    #[ORM\Column(length: 100, unique: true)]
    #[Assert\NotBlank]
    private string $slug = '';

    #[ORM\Column(type: 'text')]
    private string $description = '';

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Assert\PositiveOrZero]
    private string $totalPriceHT = '0';

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    #[Assert\PositiveOrZero]
    private ?string $packageDiscount = null; // Réduction globale du package

    #[ORM\OneToMany(targetEntity: PackageItem::class, mappedBy: 'package', cascade: ['all'], orphanRemoval: true)]
    private Collection $items;

    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\Column]
    private bool $isFeatured = false;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $validFrom = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $validUntil = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
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

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getTotalPriceHT(): string
    {
        return $this->totalPriceHT;
    }

    public function setTotalPriceHT(string $totalPriceHT): self
    {
        $this->totalPriceHT = $totalPriceHT;
        return $this;
    }

    public function getPackageDiscount(): ?string
    {
        return $this->packageDiscount;
    }

    public function setPackageDiscount(?string $packageDiscount): self
    {
        $this->packageDiscount = $packageDiscount;
        return $this;
    }

    /**
     * @return Collection<int, PackageItem>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(PackageItem $item): self
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setPackage($this);
        }
        return $this;
    }

    public function removeItem(PackageItem $item): self
    {
        if ($this->items->removeElement($item)) {
            if ($item->getPackage() === $this) {
                $item->setPackage(null);
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

    public function isFeatured(): bool
    {
        return $this->isFeatured;
    }

    public function setIsFeatured(bool $isFeatured): self
    {
        $this->isFeatured = $isFeatured;
        return $this;
    }

    public function getValidFrom(): ?\DateTimeImmutable
    {
        return $this->validFrom;
    }

    public function setValidFrom(?\DateTimeImmutable $validFrom): self
    {
        $this->validFrom = $validFrom;
        return $this;
    }

    public function getValidUntil(): ?\DateTimeImmutable
    {
        return $this->validUntil;
    }

    public function setValidUntil(?\DateTimeImmutable $validUntil): self
    {
        $this->validUntil = $validUntil;
        return $this;
    }

    public function isCurrentlyValid(): bool
    {
        $now = new \DateTimeImmutable();
        if ($this->validFrom && $this->validFrom > $now) {
            return false;
        }
        if ($this->validUntil && $this->validUntil < $now) {
            return false;
        }
        return $this->isActive;
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
