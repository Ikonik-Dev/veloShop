<?php

namespace App\Entity;

use App\Repository\BikeCompatibilityRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BikeCompatibilityRepository::class)]
#[ORM\Table(name: 'bike_compatibilities')]
#[ORM\UniqueConstraint(name: 'unique_compatibility', columns: ['bike_from_id', 'bike_to_id'])]
class BikeCompatibility
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Bike::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Bike $bikeFrom = null; // Vélo source

    #[ORM\ManyToOne(targetEntity: Bike::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Bike $bikeTo = null; // Vélo compatible

    #[ORM\Column(length: 50)]
    #[Assert\Choice(['compatible', 'similar', 'upgrade', 'downgrade'])]
    private string $type = 'compatible';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $reason = null; // Pourquoi c'est compatible

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

    public function getBikeFrom(): ?Bike
    {
        return $this->bikeFrom;
    }

    public function setBikeFrom(?Bike $bikeFrom): self
    {
        $this->bikeFrom = $bikeFrom;
        return $this;
    }

    public function getBikeTo(): ?Bike
    {
        return $this->bikeTo;
    }

    public function setBikeTo(?Bike $bikeTo): self
    {
        $this->bikeTo = $bikeTo;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): self
    {
        $this->reason = $reason;
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
