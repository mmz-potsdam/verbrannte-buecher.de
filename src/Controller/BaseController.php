<?php

// src/Controller/BaseController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\ORM\EntityManagerInterface;

use Vnn\WpApiClient\WpClient;

class BaseController extends AbstractController
{
    private $projectDir;
    protected $publicDir;
    protected $wordpressBaseUrl;

    public function __construct(string $projectDir,
                                string $publicDir,
                                string $wordpressBaseUrl)
    {
        $this->projectDir = $projectDir;
        $this->publicDir = $publicDir;
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

    protected function buildEvents(WpClient $wpClient, $limit = 4)
    {
        $events = $wpClient->events()->get(null, [
            'per_page' => 10, // TODO: add proper order
        ]);

        if (!empty($events)) {
            usort($events, function ($eventA, $eventB) {
                return strcmp($eventA['acf']['date_start'],
                              $eventB['acf']['date_start']);
            });
        }

        if (count($events) > $limit) {
            return array_slice($events, 0, $limit);
        }

        return $events;
    }

    protected function buildDigitized(Request $request,
                                      EntityManagerInterface $entityManager)
    {
        $criteria = [ 'status' => [ 1 ] ];

        $locale = $request->getLocale();
        if (!empty($locale)) {
            $criteria['language'] = \TeiEditionBundle\Utils\Iso639::code1to3($locale);
        }

        $queryBuilder = $entityManager
                ->createQueryBuilder()
                ->select('S, A')
                ->from('\TeiEditionBundle\Entity\SourceArticle', 'S')
                ->leftJoin('S.isPartOf', 'A')
                ->orderBy('S.dateCreated', 'ASC')
                ;

        foreach ($criteria as $field => $cond) {
            $queryBuilder->andWhere('S.' . $field
                                    . (is_array($cond)
                                       ? ' IN (:' . $field . ')'
                                       : '= :' . $field))
                ->setParameter($field, $cond);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Tweak CiteProc output
     */
    private function postProcessBiblio($biblio, $cslLocale)
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
     * Load and render bibliography
     */
    protected function buildBibliography($locale, $dataFname,
                                         $cslFname = 'style.csl',
                                         $additionalMarkup = [])
    {
        $cslLocale = 'en-US';

        switch ($locale) {
            case 'de':
                $cslLocale = 'de-DE';
                break;
        }

        $dataPath = join(DIRECTORY_SEPARATOR, [
            $this->getDataDir(), $dataFname,
        ]);

        if (!file_exists($dataPath)) {
            return;
        }

        $dataAsObject = json_decode(file_get_contents($dataPath));
        if (false === $dataAsObject) {
            return;
        }

        $cslPath = join(DIRECTORY_SEPARATOR, [
            $this->getDataDir(), $cslFname,
        ]);

        $citeProc = new \Seboettg\CiteProc\CiteProc(file_get_contents($cslPath), $cslLocale, $additionalMarkup);

        return $this->postProcessBiblio(@$citeProc->render($dataAsObject->data), $cslLocale);
    }
}
