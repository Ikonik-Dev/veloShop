<?php

namespace App\Service;

use App\Entity\Bike;
use App\Entity\BikeImage;
use App\Repository\BikeImageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class ImageService
{
    private string $uploadDirectory;

    public function __construct(
        private BikeImageRepository $imageRepository,
        private EntityManagerInterface $em,
        private SluggerInterface $slugger,
        string $bikeImagesDirectory,
    ) {
        $this->uploadDirectory = $bikeImagesDirectory;
    }

    /**
     * Upload et enregistrer une image pour un vélo
     */
    public function upload(Bike $bike, UploadedFile $file, string $type = 'gallery', ?string $altText = null): BikeImage
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        $file->move($this->uploadDirectory, $newFilename);

        $position = $this->getNextPosition($bike);

        $image = (new BikeImage())
            ->setBike($bike)
            ->setFilename($newFilename)
            ->setAltText($altText ?? $bike->getName())
            ->setType($type)
            ->setPosition($position);

        $this->em->persist($image);
        $this->em->flush();

        return $image;
    }

    /**
     * Supprimer une image (fichier + BDD)
     */
    public function delete(BikeImage $image): void
    {
        $filePath = $this->uploadDirectory . '/' . $image->getFilename();
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $this->em->remove($image);
        $this->em->flush();
    }

    /**
     * Image principale d'un vélo
     */
    public function getPrimaryImage(Bike $bike): ?BikeImage
    {
        foreach ($bike->getImages() as $image) {
            if ($image->getType() === 'primary' && $image->isActive()) {
                return $image;
            }
        }

        // Fallback : première image active
        foreach ($bike->getImages() as $image) {
            if ($image->isActive()) {
                return $image;
            }
        }

        return null;
    }

    /**
     * Toutes les images actives d'un vélo, ordonnées par position
     * @return BikeImage[]
     */
    public function getGallery(Bike $bike): array
    {
        return $this->imageRepository->createQueryBuilder('i')
            ->where('i.bike = :bike')
            ->andWhere('i.isActive = true')
            ->setParameter('bike', $bike)
            ->orderBy('i.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Images par type
     * @return BikeImage[]
     */
    public function getImagesByType(Bike $bike, string $type): array
    {
        return $this->imageRepository->findBy(
            ['bike' => $bike, 'type' => $type, 'isActive' => true],
            ['position' => 'ASC']
        );
    }

    /**
     * Réordonner les images d'un vélo
     * @param int[] $imageIds IDs des images dans l'ordre voulu
     */
    public function reorder(array $imageIds): void
    {
        foreach ($imageIds as $position => $imageId) {
            $image = $this->imageRepository->find($imageId);
            if ($image !== null) {
                $image->setPosition($position + 1);
            }
        }

        $this->em->flush();
    }

    /**
     * Définir une image comme image principale
     */
    public function setPrimary(BikeImage $image): void
    {
        $bike = $image->getBike();
        if ($bike === null) {
            return;
        }

        // Retirer le type "primary" des autres images
        foreach ($bike->getImages() as $existingImage) {
            if ($existingImage->getType() === 'primary') {
                $existingImage->setType('gallery');
            }
        }

        $image->setType('primary');
        $this->em->flush();
    }

    private function getNextPosition(Bike $bike): int
    {
        $maxPosition = $this->imageRepository->createQueryBuilder('i')
            ->select('MAX(i.position)')
            ->where('i.bike = :bike')
            ->setParameter('bike', $bike)
            ->getQuery()
            ->getSingleScalarResult();

        return ((int) $maxPosition) + 1;
    }
}
