<?php

namespace App\Controller\Admin;

use App\Entity\BikeImage;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class BikeImageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return BikeImage::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('url', 'URL'),
            AssociationField::new('bike', 'Vélo'),
        ];
    }
}
