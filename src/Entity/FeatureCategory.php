<?php

namespace App\Entity;

use App\Entity\BikeFeature;
use App\Repository\FeatureCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FeatureCategoryRepository::class)]
#[ORM\Table(name: 'feature_categories')]
class FeatureCategory
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

    #[ORM\OneToMany(targetEntity: BikeFeature::class, mappedBy: 'category')]
    private Collection $features;

    #[ORM\Column]
    private bool $isActive = true;

    public function __construct()
    {
        $this->features = new ArrayCollection();
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

    /**
     * @return Collection<int, BikeFeature>
     */
    public function getFeatures(): Collection
    {
        return $this->features;
    }

    public function addFeature(BikeFeature $feature): self
    {
        if (!$this->features->contains($feature)) {
            $this->features->add($feature);
            $feature->setCategory($this);
        }
        return $this;
    }

    public function removeFeature(BikeFeature $feature): self
    {
        if ($this->features->removeElement($feature)) {
            if ($feature->getCategory() === $this) {
                $feature->setCategory(null);
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
}
