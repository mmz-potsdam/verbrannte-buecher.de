<?php

// src/Controller/LibraryController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LibraryController extends BaseController
{
    /**
     * @Route("/bibliothek", name="library", options={"sitemap" = true})
     */
    public function libraryAction(Request $request)
    {
        return $this->render('Library/index.html.twig', [
            'bibliography' => $this->buildBibliography($request->getLocale(), 'verbrannte-buecher.json'),
        ]);
    }
}
