<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EnterpriseController extends AbstractController
{
    #[Route('/entreprise', name: 'app_enterprise')]
    public function index(): Response
    {
        return $this->render('enterprise/index.html.twig');
    }
}
