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
        $additionalMarkup = [
            'bibliography' => [
                'csl-entry' => function($cslItem, $renderedText) {
                    $dataAttr = '';

                    if (property_exists($cslItem, 'citation-label')
                        && !empty($cslItem->{'citation-label'}))
                    {
                        $dataAttr = sprintf(' data-csl-citation-label="%s"',
                                            htmlspecialchars($cslItem->{'citation-label'}, ENT_COMPAT, 'utf-8'));
                    }

                    return '<div id="' . $cslItem->id . '"'
                        . $dataAttr . '>'
                        . $renderedText
                        . '</div>';
                },
                'URL' => function($cslItem, $renderedText) {
                    return '<a class="arrow" href="' . $renderedText . '" target="blank" title="' . $renderedText .'">&nbsp;</a>';
                },
            ],
        ];

        $bibliography = $this->buildBibliography($request->getLocale(), 'verbrannte-buecher.json', 'style.csl', $additionalMarkup);

        return $this->render('Library/index.html.twig', [
            'bibliography' => $bibliography,
        ]);
    }
}
