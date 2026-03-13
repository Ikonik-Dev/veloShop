<?php

namespace App\Controller\Admin;

use App\Entity\BikeSpecification;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class BikeSpecificationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return BikeSpecification::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name', 'Nom'),
            AssociationField::new('bike', 'Vélo'),
        ];
    }
}
