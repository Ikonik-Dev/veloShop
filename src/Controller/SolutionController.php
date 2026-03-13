<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SolutionController extends AbstractController
{
    #[Route('/solutions', name: 'app_solutions')]
    public function index(): Response
    {
        return $this->render('solution/index.html.twig');
    }
}
