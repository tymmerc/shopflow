<?php

namespace App\Controller;

use App\Repository\CatalogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    #[Route('/home', name: 'app_home_legacy')]
    public function index(CatalogRepository $catalogRepository): Response
    {
        return $this->render('home/index.html.twig', [
            'latest' => $catalogRepository->findLatest(4),
        ]);
    }
}
