<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Documentation de l'API : le contrat docs/api/openapi.yaml affiché avec Swagger UI.
 * Le bouton « Try it out » appelle l'API du même site, donc pas de souci de CORS.
 */
final class ApiDocController extends AbstractController
{
    #[Route('/api/docs', name: 'api_docs', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('api/docs.html.twig');
    }

    #[Route('/api/openapi.yaml', name: 'api_openapi', methods: ['GET'])]
    public function contract(): Response
    {
        $file = $this->getParameter('kernel.project_dir') . '/docs/api/openapi.yaml';
        if (!is_file($file)) {
            throw new NotFoundHttpException('Contrat de l\'API introuvable.');
        }

        // Fichier de quelques Ko : une réponse normale suffit (et se teste facilement)
        return new Response((string) file_get_contents($file), Response::HTTP_OK, [
            'Content-Type' => 'application/yaml; charset=UTF-8',
        ]);
    }
}
