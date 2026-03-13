<?php

namespace App\Controller\Admin;

use App\Entity\BikeCompatibility;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class BikeCompatibilityCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return BikeCompatibility::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('bike', 'Vélo'),
        ];
    }
}
