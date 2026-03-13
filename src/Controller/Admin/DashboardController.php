<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Controller\Admin\BikeCrudController;

#[IsGranted('ROLE_ADMIN')]
#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(private AdminUrlGenerator $adminUrlGenerator) {}

    public function index(): Response
    {
        $url = $this->adminUrlGenerator->setController(BikeCrudController::class)->generateUrl();
        return $this->redirect($url);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Ramesh Log Admin')
            ->setFaviconPath('images/favicon.ico');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::section('Gestion Produits', 'fas fa-boxes');
        yield MenuItem::linkTo(\App\Controller\Admin\BikeCrudController::class, 'Vélos', 'fas fa-bicycle');
        yield MenuItem::linkTo(\App\Controller\Admin\BrandCrudController::class, 'Marques', 'fas fa-tag');
        yield MenuItem::linkTo(\App\Controller\Admin\CategoryCrudController::class, 'Catégories', 'fas fa-list');
        yield MenuItem::linkTo(\App\Controller\Admin\BikeVariantCrudController::class, 'Variantes', 'fas fa-gear');
        yield MenuItem::linkTo(\App\Controller\Admin\BikePriceCrudController::class, 'Prix', 'fas fa-dollar-sign');
        yield MenuItem::linkTo(\App\Controller\Admin\MotorCrudController::class, 'Moteurs', 'fas fa-engine');
        
        yield MenuItem::section('Stock & Ventes', 'fas fa-chart-bar');
        yield MenuItem::linkTo(\App\Controller\Admin\StockCrudController::class, 'Stock', 'fas fa-warehouse');
        yield MenuItem::linkTo(\App\Controller\Admin\ReviewCrudController::class, 'Avis', 'fas fa-star');
        // bouton retour à la boutique
        yield MenuItem::linkToRoute('Retour à la boutique', 'fas fa-store', 'app_home');
    }
}