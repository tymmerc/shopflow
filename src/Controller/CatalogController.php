<?php

namespace App\Controller;

use App\Entity\Catalog;
use App\Repository\CatalogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CatalogController extends AbstractController
{
    /** Niveaux proposés au filtre, du plus simple au plus avancé */
    private const LEVELS = ['Débutant', 'Intermédiaire', 'Confirmé'];

    #[Route('/catalog', name: 'app_catalog')]
    public function index(CatalogRepository $catalogRepository): Response
    {
        return $this->render('catalog/index.html.twig', [
            'articles' => $catalogRepository->findAll(),
            'categories' => $catalogRepository->countByCategory(),
            'levels' => self::LEVELS,
        ]);
    }

    #[Route('/catalog/{id<\d+>}', name: 'app_catalog_show')]
    public function show(Catalog $article, CatalogRepository $catalogRepository): Response
    {
        return $this->render('catalog/show.html.twig', [
            'article' => $article,
            'accessories' => $catalogRepository->findAccessories($article),
            'related' => $catalogRepository->findRelated($article, 4),
        ]);
    }
}
