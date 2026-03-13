<?php

namespace App\Controller\Admin;

use App\Entity\BikePrice;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class BikePriceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return BikePrice::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            NumberField::new('price', 'Prix'),
            AssociationField::new('bike', 'Vélo'),
        ];
    }
}
