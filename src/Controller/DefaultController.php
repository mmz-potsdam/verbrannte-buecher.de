<?php

// src/Controller/DefaultController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Doctrine\ORM\EntityManagerInterface;

use Vnn\WpApiClient\WpClient;

class DefaultController extends BaseController
{
    #[Route(path: '/', name: 'home', options: ['sitemap' => true])]
    public function homeAction(Request $request,
                               WpClient $wpClient,
                               EntityManagerInterface $entityManager)
    {
        $events = $this->buildEvents($wpClient, 4);

        $digitized = $this->buildDigitized($request, $entityManager);

        return $this->render('Default/index.html.twig', [
            'events' => $events,
            'digitized' => $digitized,
        ]);
    }

    #[Route(path: '/ueber/projekt', name: 'about-project', options: ['sitemap' => true])]
    public function aboutTeamAction(Request $request)
    {
        return $this->render('Default/about-project.html.twig');
    }

    #[Route(path: '/ueber/initiativen', name: 'about-related', options: ['sitemap' => true])]
    public function aboutRelatedAction(Request $request)
    {
        return $this->render('Default/about-related.html.twig');
    }

    #[Route(path: '/impressum', name: 'imprint')]
    public function imprintAction(Request $request)
    {
        return $this->render('Default/imprint.html.twig');
    }
}
