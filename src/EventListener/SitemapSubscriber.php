<?php

namespace App\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Presta\SitemapBundle\Event\SitemapPopulateEvent;
use Presta\SitemapBundle\Service\UrlContainerInterface;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;

class SitemapSubscriber implements EventSubscriberInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @param BlogPostRepository $blogPostRepository
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            SitemapPopulateEvent::class => 'populate',
        ];
    }

    /**
     * @param SitemapPopulateEvent $event
     */
    public function populate(SitemapPopulateEvent $event): void
    {
        $this->registerPersonUrls($event->getUrlContainer(), $event->getUrlGenerator());
        $this->registerLibraryUrls($event->getUrlContainer(), $event->getUrlGenerator());
        // TODO: register history
    }

    /**
     * @param UrlContainerInterface $urls
     * @param UrlGeneratorInterface $router
     */
    public function registerPersonUrls(UrlContainerInterface $urls, UrlGeneratorInterface $router): void
    {
        $qb = $this->entityManager
            ->createQueryBuilder();

        $qb->select([
            'P',
            "CONCAT(COALESCE(P.familyName,P.givenName), ' ', COALESCE(P.givenName, '')) HIDDEN nameSort",
        ])
            ->from('\TeiEditionBundle\Entity\Person', 'P')
            ->where('P.status IN (0,1)')
            ->orderBy('nameSort')
        ;

        foreach ($qb->getQuery()->getResult() as $person) {
            $routeName = 'person';
            $routeParams = [ 'id' => $person->getId() ];
            $gnd = $person->getGnd();
            if (!empty($gnd)) {
                $routeName .= '-by-gnd';
                $routeParams = [ 'gnd' => $gnd ];
            }

            $urls->addUrl(
                new UrlConcrete(
                    $router->generate(
                        $routeName,
                        $routeParams,
                        UrlGeneratorInterface::ABSOLUTE_URL
                    )
                ),
                'person'
            );
        }
    }

    /**
     * @param UrlContainerInterface $urls
     * @param UrlGeneratorInterface $router
     */
    public function registerLibraryUrls(UrlContainerInterface $urls, UrlGeneratorInterface $router): void
    {
        // TODO: share the following logic with BaseController
        $criteria = [ 'status' => [ 1 ] ];

        $locale = null;
        if (!empty($locale)) {
            $criteria['language'] = \TeiEditionBundle\Utils\Iso639::code1to3($locale);
        }

        $queryBuilder = $this->entityManager
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

        foreach ($queryBuilder->getQuery()->getResult() as $source) {
            $urls->addUrl(
                new UrlConcrete(
                    $router->generate(
                        'source',
                        [ 'uid' => $source->getUid() ],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    )
                ),
                'source'
            );
        }
    }
}
