<?php

// src/Controller/LibraryController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use Cocur\Slugify\SlugifyInterface;

use Doctrine\ORM\EntityManagerInterface;

class LibraryController extends BaseController
{
    /**
     * @Route("/bibliothek", name="library", options={"sitemap" = true})
     */
    public function libraryAction(Request $request,
                                  EntityManagerInterface $entityManager,
                                  UrlGeneratorInterface $urlGenerator,
                                  SlugifyInterface $slugify)
    {
        $digitized = $this->buildDigitized($request, $entityManager);
        $sourcesByCitationLabel = [];
        foreach ($digitized as $source) {
            $creatorParts = explode(', ', $source->getCreator());
            $citationLabel = join('_', [
                $slugify->slugify($creatorParts[0]),
                $source->getDateCreatedDisplay(),
            ]);
            $sourcesByCitationLabel[$citationLabel] = $source;
        }

        $options = [
            'urlGenerator' => $urlGenerator,
            'publicDir' => $this->publicDir,
            'basePath' => $request->getBasePath(),
        ];

        $additionalMarkup = [
            'bibliography' => [
                'csl-entry' => function ($cslItem, $renderedText) use ($sourcesByCitationLabel, $options) {
                    $classes = [];
                    $thumb = '';

                    if (property_exists($cslItem, 'URL')
                        && !empty($cslItem->{'URL'}))
                    {
                        // so we can filter
                        $classes[] = 'online';
                    }

                    if (property_exists($cslItem, 'citation-label')
                        && !empty($cslItem->{'citation-label'}))
                    {
                        if (array_key_exists($cslItem->{'citation-label'}, $sourcesByCitationLabel)) {
                            $source = $sourcesByCitationLabel[$cslItem->{'citation-label'}];
                            $classes[] = 'digital-library';
                            $url = $options['urlGenerator']->generate('source', [
                                'uid' => $source->getUid(),
                            ]);

                            if (!in_array('online', $classes)) {
                                // append internal link
                                $renderedText .= '<a class="arrow" href="' . $url . '" title="Digitale Bibliothek">&nbsp;</a>';
                            }

                            // thumb
                            $thumbPath =  sprintf('/viewer/source-%05d/thumb.jpg',
                                                  str_replace('source-', '', $source->getUid()));
                            if (file_exists($options['publicDir'] . $thumbPath)) {
                                $thumb = sprintf('<div class="thumb"><a href="%s"><img src="%s" /></a></div>',
                                                 $url, $options['basePath'] . $thumbPath);
                            }
                        }
                    }

                    return '<div id="' . $cslItem->id . '"'
                        . (!empty($classes) ? ' class="' . join(' ', $classes) . '"' : '')
                        . '>'
                        . $thumb
                        . $renderedText
                        . '</div>';
                },
                'URL' => function ($cslItem, $renderedText) use ($sourcesByCitationLabel, $options) {
                    $url = $renderedText;
                    $target = ' target="blank"';

                    if (array_key_exists($cslItem->{'citation-label'}, $sourcesByCitationLabel)) {
                        // use internal link
                        $source = $sourcesByCitationLabel[$cslItem->{'citation-label'}];
                        $url = $options['urlGenerator']->generate('source', [
                            'uid' => $source->getUid(),
                        ]);
                        $renderedText = 'Digitale Bibliothek';
                        $target = '';
                    }

                    return '<a class="arrow" href="' . $url . '" title="' . $renderedText .'"' . $target . '>&nbsp;</a>';
                },
            ],
        ];

        $bibliography = $this->buildBibliography($request->getLocale(), 'verbrannte-buecher.json', 'style.csl', $additionalMarkup);

        return $this->render('Library/index.html.twig', [
            'bibliography' => $bibliography,
        ]);
    }
}
