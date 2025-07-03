<?php

// src/Controller/TeachingController.php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TeachingController extends BaseController
{
    #[Route(path: '/bildung', name: 'teaching', options: ['sitemap' => true])]
    public function indexAction(Request $request): Response
    {
        return $this->render('Teaching/index.html.twig', [
        ]);
    }
}
