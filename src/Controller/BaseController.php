<?php

// src/Controller/BaseController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Vnn\WpApiClient\WpClient;

class BaseController extends AbstractController
{
    private $projectDir;
    protected $wordpressBaseUrl;

    public function __construct(string $projectDir, string $wordpressBaseUrl)
    {
        $this->projectDir = $projectDir;
        $this->wordpressBaseUrl = $wordpressBaseUrl;
    }

    public function getProjectDir()
    {
        return $this->projectDir;
    }

    public function getDataDir()
    {
        return $this->projectDir . '/data';
    }

    protected function buildEvents(WpClient $wpClient)
    {
        $events = $wpClient->events()->get(null, [
            'per_page' => 4,
        ]);

        if (!empty($events)) {
            usort($events, function ($eventA, $eventB) {
                return strcmp($eventA['acf']['date_start'],
                              $eventB['acf']['date_start']);
            });
        }

        return $events;
    }
}