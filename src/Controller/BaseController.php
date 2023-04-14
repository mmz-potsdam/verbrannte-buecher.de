<?php

// src/Controller/BaseController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
}