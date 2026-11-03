<?php

namespace App\Controller;

use App\Repository\CatalogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CatalogController extends AbstractController
{
    #[Route('/catalog', name: 'app_catalog')]
    public function index(CatalogRepository $catalogRepository): Response
    {
        return $this->render('catalog/index.html.twig', [
            'articles' => $catalogRepository->findAll(),
        ]);
    }
}
