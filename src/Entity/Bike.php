<?php

namespace App\Entity;

use App\Repository\BikeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BikeRepository::class)]
#[ORM\Table(name: 'bikes')]
#[ORM\Index(columns: ['slug'])]
class Bike
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

    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'bikes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    #[ORM\ManyToOne(targetEntity: Brand::class, inversedBy: 'bikes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Brand $brand = null;

    #[ORM\Column(type: 'text')]
    private string $description = '';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $features = null; // Bullet points

    #[ORM\Column(nullable: true)]
    private ?int $modelYear = null;

    #[ORM\OneToMany(targetEntity: BikeVariant::class, mappedBy: 'bike', cascade: ['all'], orphanRemoval: true)]
    private Collection $variants;

    #[ORM\OneToMany(targetEntity: BikeImage::class, mappedBy: 'bike', cascade: ['all'], orphanRemoval: true)]
    private Collection $images;

    #[ORM\OneToMany(targetEntity: Review::class, mappedBy: 'bike', cascade: ['all'], orphanRemoval: true)]
    private Collection $reviews;

    #[ORM\ManyToMany(targetEntity: BikeFeature::class)]
    #[ORM\JoinTable(name: 'bikes_equipments')]
    private Collection $bikeFeatures;

    #[ORM\Column(length: 50)]
    #[Assert\Choice(['none', 'semi-pro', 'pro', 'enterprise'])]
    private string $segmentLevel = 'none'; // Level de spécialisation

    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\Column]
    private bool $isFeatured = false;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->variants = new ArrayCollection();
        $this->images = new ArrayCollection();
        $this->reviews = new ArrayCollection();
        $this->bikeFeatures = new ArrayCollection();
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

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;
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

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getFeatures(): ?string
    {
        return $this->features;
    }

    public function setFeatures(?string $features): self
    {
        $this->features = $features;
        return $this;
    }

    public function getModelYear(): ?int
    {
        return $this->modelYear;
    }

    public function setModelYear(?int $modelYear): self
    {
        $this->modelYear = $modelYear;
        return $this;
    }

    /**
     * @return Collection<int, BikeVariant>
     */
    public function getVariants(): Collection
    {
        return $this->variants;
    }

    public function addVariant(BikeVariant $variant): self
    {
        if (!$this->variants->contains($variant)) {
            $this->variants->add($variant);
            $variant->setBike($this);
        }
        return $this;
    }

    public function removeVariant(BikeVariant $variant): self
    {
        if ($this->variants->removeElement($variant)) {
            if ($variant->getBike() === $this) {
                $variant->setBike(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, BikeImage>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(BikeImage $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setBike($this);
        }
        return $this;
    }

    public function removeImage(BikeImage $image): self
    {
        if ($this->images->removeElement($image)) {
            if ($image->getBike() === $this) {
                $image->setBike(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Review>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): self
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews->add($review);
            $review->setBike($this);
        }
        return $this;
    }

    public function removeReview(Review $review): self
    {
        if ($this->reviews->removeElement($review)) {
            if ($review->getBike() === $this) {
                $review->setBike(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, BikeFeature>
     */
    public function getBikeFeatures(): Collection
    {
        return $this->bikeFeatures;
    }

    public function addBikeFeature(BikeFeature $bikeFeature): self
    {
        if (!$this->bikeFeatures->contains($bikeFeature)) {
            $this->bikeFeatures->add($bikeFeature);
        }
        return $this;
    }

    public function removeBikeFeature(BikeFeature $bikeFeature): self
    {
        $this->bikeFeatures->removeElement($bikeFeature);
        return $this;
    }

    public function getSegmentLevel(): string
    {
        return $this->segmentLevel;
    }

    public function setSegmentLevel(string $segmentLevel): self
    {
        $this->segmentLevel = $segmentLevel;
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
