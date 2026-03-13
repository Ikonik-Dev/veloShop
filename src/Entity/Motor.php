<?php

namespace App\Entity;

use App\Repository\MotorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MotorRepository::class)]
#[ORM\Table(name: 'motors')]
class Motor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    private string $name = '';

    #[ORM\ManyToOne(targetEntity: Brand::class, inversedBy: 'motors')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Brand $brand = null;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    private int $wattage = 0;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero]
    private ?int $torque = null; // en Nm

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero]
    private ?int $batteryCapacity = null; // en Wh

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero]
    private ?int $estimatedRange = null; // en km

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\OneToMany(targetEntity: BikeVariant::class, mappedBy: 'motor')]
    private Collection $bikeVariants;

    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->bikeVariants = new ArrayCollection();
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

    public function getBrand(): ?Brand
    {
        return $this->brand;
    }

    public function setBrand(?Brand $brand): self
    {
        $this->brand = $brand;
        return $this;
    }

    public function getWattage(): int
    {
        return $this->wattage;
    }

    public function setWattage(int $wattage): self
    {
        $this->wattage = $wattage;
        return $this;
    }

    public function getTorque(): ?int
    {
        return $this->torque;
    }

    public function setTorque(?int $torque): self
    {
        $this->torque = $torque;
        return $this;
    }

    public function getBatteryCapacity(): ?int
    {
        return $this->batteryCapacity;
    }

    public function setBatteryCapacity(?int $batteryCapacity): self
    {
        $this->batteryCapacity = $batteryCapacity;
        return $this;
    }

    public function getEstimatedRange(): ?int
    {
        return $this->estimatedRange;
    }

    public function setEstimatedRange(?int $estimatedRange): self
    {
        $this->estimatedRange = $estimatedRange;
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

    /**
     * @return Collection<int, BikeVariant>
     */
    public function getBikeVariants(): Collection
    {
        return $this->bikeVariants;
    }

    public function addBikeVariant(BikeVariant $bikeVariant): self
    {
        if (!$this->bikeVariants->contains($bikeVariant)) {
            $this->bikeVariants->add($bikeVariant);
            $bikeVariant->setMotor($this);
        }
        return $this;
    }

    public function removeBikeVariant(BikeVariant $bikeVariant): self
    {
        if ($this->bikeVariants->removeElement($bikeVariant)) {
            if ($bikeVariant->getMotor() === $this) {
                $bikeVariant->setMotor(null);
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
