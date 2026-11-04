<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Crédits des photos produit (Wikimedia Commons). Les licences CC BY et CC BY-SA
 * exigent de citer l'auteur, la licence et la source.
 */
final class CreditsController extends AbstractController
{
    #[Route('/credits', name: 'app_credits')]
    public function index(): Response
    {
        $file = $this->getParameter('kernel.project_dir') . '/public/images/products/credits.json';
        $credits = is_file($file) ? json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR) : [];

        return $this->render('credits/index.html.twig', ['credits' => $credits]);
    }
}
