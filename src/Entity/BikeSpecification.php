<?php

namespace App\Entity;

use App\Repository\BikeSpecificationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BikeSpecificationRepository::class)]
#[ORM\Table(name: 'bike_specifications')]
class BikeSpecification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: BikeVariant::class, inversedBy: 'specifications')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?BikeVariant $variant = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    private string $name = ''; // Ex: "Cadre", "Fourche", "Freins", "Dérailleur"...

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    private string $value = ''; // Ex: "Aluminium 6061", "Suspension avant 100mm"...

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $unit = null; // Ex: "mm", "kg", "pouces"...

    #[ORM\Column]
    private int $position = 0; // Ordre d'affichage

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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;
        return $this;
    }

    public function getUnit(): ?string
    {
        return $this->unit;
    }

    public function setUnit(?string $unit): self
    {
        $this->unit = $unit;
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

    public function getDisplayValue(): string
    {
        return $this->unit ? $this->value . ' ' . $this->unit : $this->value;
    }
}
