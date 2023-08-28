<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class BaseController extends AbstractController
{
    #[Route('/base', name: 'app_base')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Bienvenue !',
            'path' => 'src/Controller/BaseController.php',
        ]);
    }
}
