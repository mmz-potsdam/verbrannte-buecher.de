<?php

// src/Controller/LibraryController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LibraryController extends BaseController
{
    /**
     * Tweak CiteProc output
     */
    protected function postProcessBiblio($biblio, $cslLocale)
    {
        if ('de-DE' == $cslLocale) {
            // . übersetzt von doesn't get properly capitalized
            $biblio = str_replace('. übersetzt von', '. Übersetzt von ', $biblio);
            // improper spacing
            $biblio = str_replace('. Herausgegeben von', '. Herausgegeben von ', $biblio);
        }

        /* vertical-align: super doesn't render nicely:
           http://stackoverflow.com/a/1530819/2114681
        */
        $biblio = preg_replace('/style="([^"]*)vertical\-align\:\s*super;([^"]*)"/',
                               'style="\1vertical-align: top; font-size: 66%;\2"', $biblio);

        return $biblio;
    }

    /**
     * Render bibliography
     */
    public function buildBibliography($locale)
    {
        $fname = 'apa-no-initials-place.csl';
        $cslLocale = 'en-US';

        switch ($locale) {
            case 'de':
                $cslLocale = 'de-DE';
                break;
        }

        $dataPath = join(DIRECTORY_SEPARATOR, [
            $this->getDataDir(), 'verbrannte-buecher.json',
        ]);

        if (!file_exists($dataPath)) {
            return;
        }

        $dataAsObject = json_decode(file_get_contents($dataPath));
        if (false === $dataAsObject) {
            return;
        }

        $cslPath = join(DIRECTORY_SEPARATOR, [
            $this->getDataDir(), $fname,
        ]);

        $citeProc = new \Seboettg\CiteProc\CiteProc(file_get_contents($cslPath), $cslLocale);

        return  $this->postProcessBiblio(@$citeProc->render($dataAsObject->data), $cslLocale);
    }

    /**
     * @Route("/bibliothek", name="library", options={"sitemap" = true})
     */
    public function libraryAction(Request $request)
    {
        return $this->render('Library/index.html.twig', [
            'bibliography' => $this->buildBibliography($request->getLocale()),
        ]);
    }
}
