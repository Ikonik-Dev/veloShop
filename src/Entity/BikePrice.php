<?php

namespace App\Entity;

use App\Repository\BikePriceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BikePriceRepository::class)]
#[ORM\Table(name: 'bike_prices')]
#[ORM\UniqueConstraint(name: 'unique_variant_segment', columns: ['variant_id', 'segment_id'])]
class BikePrice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: BikeVariant::class, inversedBy: 'prices')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?BikeVariant $variant = null;

    #[ORM\ManyToOne(targetEntity: CustomerSegment::class, inversedBy: 'bikePrices')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CustomerSegment $segment = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Assert\PositiveOrZero]
    private string $priceHT = '0'; // Prix HT

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    #[Assert\PositiveOrZero]
    private ?string $priceTTC = null; // Prix TTC (calculé auto si null)

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2, nullable: true)]
    #[Assert\Range(min: '0')]
    private ?string $marginRate = null; // Marge en pourcentage

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $validFrom = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $validUntil = null;

    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
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

    public function getSegment(): ?CustomerSegment
    {
        return $this->segment;
    }

    public function setSegment(?CustomerSegment $segment): self
    {
        $this->segment = $segment;
        return $this;
    }

    public function getPriceHT(): string
    {
        return $this->priceHT;
    }

    public function setPriceHT(string $priceHT): self
    {
        $this->priceHT = $priceHT;
        return $this;
    }

    public function getPriceTTC(): ?string
    {
        return $this->priceTTC;
    }

    public function setPriceTTC(?string $priceTTC): self
    {
        $this->priceTTC = $priceTTC;
        return $this;
    }

    public function getMarginRate(): ?string
    {
        return $this->marginRate;
    }

    public function setMarginRate(?string $marginRate): self
    {
        $this->marginRate = $marginRate;
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
}
