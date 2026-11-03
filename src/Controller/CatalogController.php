<?php

namespace App\Controller;

use App\Entity\Catalog;
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
            'articles' => $catalogRepository->findAllCatalogs(),
        ]);
    }

    #[Route('/catalog/{id<\d+>}', name: 'app_catalog_show')]
    public function show(Catalog $article): Response
    {
        return $this->render('catalog/show.html.twig', [
            'article' => $article,
        ]);
    }
}
