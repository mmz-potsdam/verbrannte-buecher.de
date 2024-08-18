<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use TeiEditionBundle\Entity\Organization;
use TeiEditionBundle\Entity\Person;
use TeiEditionBundle\Entity\Place;

/**
 * Bibliographic Item
 *
 * See also [blog post](http://blog.schema.org/2014/09/schemaorg-support-for-bibliographic_2.html).
 *
 * @see http://schema.org/CreativeWork and derived documents Documentation on Schema.org
 *
 */
class CreativeWork extends SchemaOrg
{
    protected static function buildCorresp($zoteroData, $zoteroMeta)
    {
        $slugify = new \Cocur\Slugify\Slugify();

        if (!empty($zoteroData['extra'])
            && preg_match('/[a-z\-0-9]+_[0-9][0-9a-z]*/', $zoteroData['extra'])) {
            // manually set
            return $zoteroData['extra'];
        }

        $creator = !empty($zoteroMeta['creatorSummary'])
            ? $zoteroMeta['creatorSummary'] : 'NN';

        if (!empty($zoteroMeta['parsedDate'])
            && preg_match('/^(\d+)/', $zoteroMeta['parsedDate'], $matches)) {
            $date = $matches[1];
        }
        else {
            $date = 'nd'; // no date given, n.d.
        }

        return $slugify->slugify($creator, '-') . '_' . $date;
    }

    public static function fromZotero($zoteroData, $zoteroMeta = null)
    {
        $creativeWork = new CreativeWork();

        if (!is_null($zoteroMeta)) {
            $creativeWork->setSlug(self::buildCorresp($zoteroData, $zoteroMeta));
        }

        if ('journalArticle' == $zoteroData['itemType']) {
            // so we can setIssn
            $creativeWork->setIsPartOf(new Periodical());
        }

        foreach ([
            'key' => 'id',
            'version' => 'version',
            'itemType' => 'itemType',
            'title' => 'name',
            'bookTitle' => 'containerName',
            'encyclopediaTitle' => 'containerName',
            'publicationTitle' => 'containerName',
            'creators' => 'creators',
            'ISSN' => 'issn',
            'series' => 'series',
            'seriesNumber' => 'seriesNumber',
            'volume' => 'volume',
            'numberOfVolumes' => 'numberOfVolumes',
            'issue' => 'issue',
            'edition' => 'bookEdition',
            'place' => 'publicationLocation',
            'publisher' => 'publisher',
            'date' => 'datePublished',
            'pages' => 'pagination',
            'numPages' => 'numberOfPages',
            'language' => 'language',
            'DOI' => 'doi',
            'ISBN' => 'isbn',
            'url' => 'url',
            'accessDate' => 'dateAccessed',

            'dateAdded' => 'createdAt',
            'dateModified' => 'changedAt',
        ] as $src => $target) {
            $val = array_key_exists($src, $zoteroData) ? $zoteroData[$src] : null;
            if (is_null($val) && 'containerName' == $target) {
                // skip on null since multiple $src can set this
                continue;
            }

            $methodName = 'set' . ucfirst($target);
            $creativeWork->$methodName($val);
        }

        return $creativeWork;
    }

    /**
     * Build a list of normalized ISBNs of the book.
     *
     * @return array
     */
    public static function buildIsbnListNormalized($isbn, $hyphens = true)
    {
        $normalized = [];
        if (empty($isbn)) {
            return $normalized;
        }

        $isbnUtil = new \Isbn\Isbn();

        $candidates = preg_split('/\s+/', $isbn);
        foreach ($candidates as $candidate) {
            if (preg_match('/([0-9xX\-]+)/', $candidate, $matches)) {
                $type = $isbnUtil->check->identify($matches[1]);
                if (false !== $type) {
                    $isbn13 = 13 == $type
                        ? $matches[1]
                        : $isbnUtil->translate->to13($matches[1]);

                    if (true === $hyphens) {
                        $isbn13 = $isbnUtil->hyphens->fixHyphens($isbn13);
                    }
                    else if (false === $hyphens) {
                        $isbn13 = $isbnUtil->hyphens->removeHyphens($isbn13);
                    }

                    if (!in_array($isbn13, $normalized)) {
                        $normalized[] = $isbn13;
                    }
                }
            }
        }

        return $normalized;
    }

    /**
     * Build both ISBN-10 and ISBN-13.
     *
     * @return array
     */
    public static function buildIsbnVariants($isbn, $hyphens = true)
    {
        $variants = [];

        $isbnUtil = new \Isbn\Isbn();

        $type = $isbnUtil->check->identify($isbn);
        if (false === $type) {
            return $variants;
        }

        $isbn10 = 13 == $type ? $isbnUtil->translate->to10($isbn) : $isbn;
        if (false !== $isbn10) {
            if (true === $hyphens) {
                $isbn10 = $isbnUtil->hyphens->fixHyphens($isbn10);
            }
            else if (false === $hyphens) {
                $isbn10 = $isbnUtil->hyphens->removeHyphens($isbn10);
            }

            $variants[] = $isbn10;
        }

        $isbn13 = 13 == $type ? $isbn : $isbnUtil->translate->to13($isbn);

        if (true === $hyphens) {
            $isbn13 = $isbnUtil->hyphens->fixHyphens($isbn13);
        }
        else if (false === $hyphens) {
            $isbn13 = $isbnUtil->hyphens->removeHyphens($isbn13);
        }

        $variants[] = $isbn13;

        return $variants;
    }

    /**
     * @var string The type of the Bibliographic Item (as in Zotero)
     */
    protected $itemType;

    /**
     * @var array The author/contributor/editor of this CreativeWork.
     */
    protected $creators;

    /**
     * @var string The series of books the book was published in
     */
    protected $series;

    /**
     * @var string The number within the series of books the book was published in
     */
    protected $seriesNumber;

    /**
     * @var string The volume of a journal or multi-volume book
     */
    protected $volume;

    /**
     * @var string The number of volumes of a multi-volume book
     */
    protected $numberOfVolumes;

    /**
     * @var string The issue of a journal, magazine, or tech-report, if applicable
     */
    protected $issue;

    /**
     * @var string The edition of a book
     */
    protected $bookEdition;

    /**
     * @var string The place(s) of publication
     */
    protected $publicationLocation; /* map to contentLocation in Schema.org */

    /**
     * @var string The publisher's name
     */
    protected $publisher;

    /**
     * @var string Date of first broadcast/publication.
     */
    protected $datePublished;

    /**
     * @var string The Page numbers, separated either by commas or as range by hyphen
     */
    protected $pagination;

    /**
     * @var string The number of pages of the book
     */
    protected $numberOfPages;

    /**
     * @var string The doi of the article
     */
    protected $doi;

    /**
     * @var string The isbn of the book
     */
    protected $isbn;

    /**
     * @var array
     */
    protected $additional;

    /**
     * @var CreativeWork Indicates a Bibitem that this Bibitem is (in some sense) part of.
     */
    protected $isPartOf;

    /**
     * @var string The title of the book or journal for bookSection / journalArticle.
     */
    protected $containerName;

    /**
     * @var string
     */
    #[Assert\Type(type: 'string')]
    #[Assert\NotNull]
    protected $language;

    /**
     * @var string URL of the item.
     */
    #[Assert\Url]
    protected $url;

    /**
     * @var string Non-Schema.org Date of URL-access.
     */
    protected $dateAccessed;

    /**
     * @var string
     */
    #[Assert\Type(type: 'string')]
    protected $slug;

    /**
     * @var string
     *
     */
    protected $version;

    public static function slugifyCorresp($slugify, $corresp)
    {
        if (preg_match('/(.*)\_(\d+[^_*])/', $corresp, $matches)) {
            // keep underscores before date
            return $slugify->slugify($matches[1])
                 . '_'
                 . $slugify->slugify($matches[2]);
        }

        return $slugify->slugify($corresp, '-');
    }

    /**
     * Sets creators.
     *
     * @param array $creators
     *
     * @return $this
     */
    public function setCreators($creators = null)
    {
        $this->creators = $creators;

        return $this;
    }

    /**
     * Gets creators.
     *
     * @return array
     */
    public function getCreators()
    {
        return $this->creators;
    }

    /**
     * Sets series.
     *
     * @param string|null $series
     *
     * @return $this
     */
    public function setSeries($series = null)
    {
        $this->series = $series;

        return $this;
    }

    /**
     * Gets series.
     *
     * @return string|null
     */
    public function getSeries()
    {
        return $this->series;
    }

    /**
     * Sets series number.
     *
     * @param string|null $seriesNumber
     *
     * @return $this
     */
    public function setSeriesNumber($seriesNumber = null)
    {
        $this->seriesNumber = $seriesNumber;

        return $this;
    }

    /**
     * Gets series number.
     *
     * @return string|null
     */
    public function getSeriesNumber()
    {
        return $this->seriesNumber;
    }

    /**
     * Sets volume.
     *
     * @param string|null $volume
     *
     * @return $this
     */
    public function setVolume($volume = null)
    {
        $this->volume = $volume;

        return $this;
    }

    /**
     * Gets volume.
     *
     * @return string|null
     */
    public function getVolume()
    {
        return $this->volume;
    }

    /**
     * Sets number of volumes.
     *
     * @param string|null $numberOfVolumes
     *
     * @return $this
     */
    public function setNumberOfVolumes($numberOfVolumes = null)
    {
        $this->numberOfVolumes = $numberOfVolumes;

        return $this;
    }

    /**
     * Gets number of volumes.
     *
     * @return string|null
     */
    public function getNumberOfVolumes()
    {
        return $this->numberOfVolumes;
    }

    /**
     * Sets issue.
     *
     * @param string|null $issue
     *
     * @return $this
     */
    public function setIssue($issue = null)
    {
        $this->issue = $issue;

        return $this;
    }

    /**
     * Gets issue.
     *
     * @return string|null
     */
    public function getIssue()
    {
        return $this->issue;
    }

    /**
     * Sets edition of the book.
     *
     * @param string|null $bookEdition
     *
     * @return $this
     */
    public function setBookEdition($bookEdition = null)
    {
        $this->bookEdition = $bookEdition;

        return $this;
    }

    /**
     * Gets book edition.
     *
     * @return string|null
     */
    public function getBookEdition()
    {
        return $this->bookEdition;
    }

    /**
     * Sets publication location.
     *
     * @param string|null $publicationLocation
     *
     * @return $this
     */
    public function setPublicationLocation($publicationLocation = null)
    {
        $this->publicationLocation = $publicationLocation;

        return $this;
    }

    /**
     * Gets publication location.
     *
     * @return string|null
     */
    public function getPublicationLocation()
    {
        return $this->publicationLocation;
    }

    /**
     * Sets publisher.
     *
     * @param string|null $publisher
     *
     * @return $this
     */
    public function setPublisher($publisher = null)
    {
        $this->publisher = $publisher;

        return $this;
    }

    /**
     * Gets publisher.
     *
     * @return string|null
     */
    public function getPublisher()
    {
        return $this->publisher;
    }

    /**
     * Sets datePublished.
     *
     * @param string|null $datePublished
     *
     * @return $this
     */
    public function setDatePublished($datePublished = null)
    {
        $this->datePublished = $datePublished;

        return $this;
    }

    /**
     * Gets datePublished.
     *
     * @return string|null
     */
    public function getDatePublished()
    {
        return $this->datePublished;
    }

    /**
     * Sets pagination.
     *
     * @param string|null $pagination
     *
     * @return $this
     */
    public function setPagination($pagionation = null)
    {
        $this->pagination = $pagionation;

        return $this;
    }

    /**
     * Gets pagination.
     *
     * @return string|null
     */
    public function getPagination()
    {
        return $this->pagination;
    }

    /**
     * Sets number of pages.
     *
     * @param string|null $numberOfPages
     *
     * @return $this
     */
    public function setNumberOfPages($numberOfPages = null)
    {
        $this->numberOfPages = $numberOfPages;

        return $this;
    }

    /**
     * Gets number of pages.
     *
     * @return string|null
     */
    public function getNumberOfPages()
    {
        return $this->numberOfPages;
    }

    /**
     * Sets the DOI of the publication.
     *
     * @param string|null $doi
     *
     * @return $this
     */
    public function setDoi($doi = null)
    {
        $this->doi = $doi;

        return $this;
    }

    /**
     * Gets the DOI of the publication.
     *
     * @return string|null
     */
    public function getDoi()
    {
        return $this->doi;
    }

    /**
     * Sets the ISBN of the book.
     *
     * @param string|null $isbn
     *
     * @return $this
     */
    public function setIsbn($isbn = null)
    {
        $this->isbn = $isbn;

        return $this;
    }

    /**
     * Gets ISBN of the book.
     *
     * @return string|null
     */
    public function getIsbn()
    {
        return $this->isbn;
    }

    /**
     * Gets a list of normalized ISBNs of the book.
     *
     * @return array
     */
    public function getIsbnListNormalized($hyphens = true)
    {
        return self::buildIsbnListNormalized($this->isbn, $hyphens);
    }

    /**
     * Sets itemType.
     *
     * @param string $itemType
     *
     * @return $this
     */
    public function setItemType($itemType)
    {
        $this->itemType = $itemType;

        return $this;
    }

    /**
     * Gets itemType.
     *
     * @return string
     */
    public function getItemType()
    {
        return $this->itemType;
    }

    /**
     * Sets isPartOf.
     *
     * @param CreativeWork|null $isPartOf
     *
     * @return $this
     */
    public function setIsPartOf(?CreativeWork $isPartOf = null)
    {
        $this->isPartOf = $isPartOf;

        return $this;
    }

    /**
     * Gets isPartOf.
     *
     * @return CreativeWork|null
     */
    public function getIsPartOf()
    {
        return $this->isPartOf;
    }

    /**
     * Sets container name.
     *
     * @param string|null $containerName
     *
     * @return $this
     */
    public function setContainerName($containerName = null)
    {
        $this->containerName = $containerName;
        if (isset($this->isPartOf)) {
            $this->isPartOf->setName($containerName);
        }

        return $this;
    }

    /**
     * Gets container name.
     *
     * @return string|null
     */
    public function getContainerName()
    {
        if (isset($this->isPartOf)) {
            return $this->isPartOf->getName();
        }

        return $this->containerName;
    }

    /**
     * Sets ISSN.
     *
     * @param string|null $issn
     *
     * @return $this
     */
    public function setIssn($issn = null)
    {
        if (isset($this->isPartOf) && $this->isPartOf instanceof Periodical) {
            $this->isPartOf->setIssn($issn);
        }

        return $this;
    }

    /**
     * Gets issn.
     *
     * @return string|null
     */
    public function getIssn()
    {
        if (isset($this->isPartOf) && $this->isPartOf instanceof Periodical) {
            return $this->isPartOf->getIssn();
        }
    }

    /**
     * Sets language.
     *
     * @param string|null $language
     *
     * @return $this
     */
    public function setLanguage($language = null)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Gets language.
     *
     * @return string|null
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Sets dateAccessed.
     *
     * @param string|null $dateAccessed
     *
     * @return $this
     */
    public function setDateAccessed($dateAccessed = null)
    {
        $this->dateAccessed = $dateAccessed;

        return $this;
    }

    /**
     * Gets dateAccessed.
     *
     * @return string|null
     */
    public function getDateAccessed()
    {
        return $this->dateAccessed;
    }

    /**
     * Sets slug.
     *
     * @param string|null $slug
     *
     * @return $this
     */
    public function setSlug($slug = null)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Gets slug.
     *
     * @return string|null
     */
    public function getSlug($fallback = false)
    {
        return $this->slug;
    }

    /**
     * Sets version.
     *
     * @param string|null $version
     *
     * @return $this
     */
    public function setVersion($version = null)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Gets version.
     *
     * @return string|null
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Gets cover URL.
     *
     * @return string|null
     */
    public function getCoverUrl()
    {
        return null; // not implemented yet
    }

    public function renderCitationAsHtml($citeProc, $locale, $purgeSeparator = false)
    {
        $ret = @$citeProc->render([ json_decode(json_encode($this->jsonSerialize($locale))) ]);

        /* vertical-align: super doesn't render nicely:
           http://stackoverflow.com/a/1530819/2114681
        */
        $ret = preg_replace(
            '/style="([^"]*)vertical\-align\:\s*super;([^"]*)"/',
            'style="\1vertical-align: top; font-size: 66%;\2"',
            $ret
        );

        if ($purgeSeparator) {
            if (preg_match('/, <span class="citeproc\-in">/', $ret, $matches)) {
                $ret = preg_replace('/, (<span class="citeproc\-in">)/', '\1', $ret);
            }
            else if (preg_match('/, <span class="citeproc\-volumes">/', $ret, $matches)) {
                $ret = preg_replace('/, (<span class="citeproc\-volumes">)/', '\1', $ret);
            }
            else if (preg_match('/, <span class="citeproc\-book\-series">/', $ret, $matches)) {
                $ret = preg_replace('/, (<span class="citeproc\-book\-series">)/', '\1', $ret);
            }
            else if (preg_match('/, <span class="citeproc\-place">/', $ret, $matches)) {
                $ret = preg_replace('/, (<span class="citeproc\-place">)/', '\1', $ret);
            }
            else if (preg_match('/, <span class="citeproc\-date">/', $ret, $matches)) {
                $ret = preg_replace('/, (<span class="citeproc\-date">)/', '\1', $ret);
            }

            // make links clickable
            $ret = preg_replace_callback(
                '/(<span class="citeproc\-URL">&lt;)(.*?)(&gt;)/',
                function ($matches) {
                    return $matches[1]
                        . sprintf(
                            '<a href="%s" target="_blank">%s</a>',
                            $matches[2],
                            $matches[2]
                        )
                        . $matches[3];
                },
                $ret
            );

            // make doi: clickable
            $ret = preg_replace_callback(
                '/(<span class="citeproc\-DOI">&lt;)doi\:(.*?)(&gt;)/',
                function ($matches) {
                    return $matches[1]
                        . sprintf(
                            '<a href="https://dx.doi.org/%s" target="_blank">doi:%s</a>',
                            $matches[2],
                            $matches[2]
                        )
                        . $matches[3];
                },
                $ret
            );
        }

        return $ret;
    }

    private static function mb_ucfirst($string, $encoding = 'UTF-8')
    {
        $strlen = mb_strlen($string, $encoding);
        $firstChar = mb_substr($string, 0, 1, $encoding);
        $then = mb_substr($string, 1, $strlen - 1, $encoding);

        return mb_strtoupper($firstChar, $encoding) . $then;
    }

    private static function adjustTitle($title)
    {
        if (!is_null($title) && preg_match('/\s*\:\s+/', $title)) {
            // we don't separate subtitle by ': ' but by '. ';
            $titleParts = preg_split('/\s*\:\s+/', $title, 2);
            $title = implode('. ', [ $titleParts[0], self::mb_ucfirst($titleParts[1]) ]);
        }

        return $title;
    }

    private static function adjustPublisherPlace($place, $locale)
    {
        if ('en' == $locale) {
            $place = preg_replace('/u\.\s*a\./', 'et al.', $place);
        }

        return $place;
    }

    private function parseLocalizedDate($dateStr, $locale = 'de_DE', $pattern = 'dd. MMMM yyyy')
    {
        if (function_exists('intl_is_failure')) {
            // modern method
            $formatter = new \IntlDateFormatter($locale, \IntlDateFormatter::FULL, \IntlDateFormatter::FULL);
            $formatter->setPattern($pattern);
            $dateObj = \DateTime::createFromFormat('U', $formatter->parse($dateStr));
            if (false !== $dateObj) {
                return [
                    'year' => (int) $dateObj->format('Y'),
                    'month' =>  (int) $dateObj->format('m'),
                    'day' => (int) $dateObj->format('d'),
                ];
            }
        }

        // longer but probably more robust
        static $monthNamesLocalized = [];

        if ('en_US' != $locale) {
            // replace localized month-names with english once

            if (!array_key_exists('en_US', $monthNamesLocalized)) {
                $months = [];
                $currentLocale = setlocale(LC_TIME, 'en_US');
                for ($month = 0; $month < 12; $month++) {
                    $months[] =  strftime('%B', mktime(0, 0, 0, $month + 1));
                }
                $monthNamesLocalized['en_US'] = $months;
                setlocale(LC_TIME, $currentLocale);
            }

            if (!array_key_exists($locale, $monthNamesLocalized)) {
                $months = [];
                $currentLocale = setlocale(LC_TIME, $locale . '.utf8');
                for ($month = 0; $month < 12; $month++) {
                    $months[] = strftime('%B', mktime(0, 0, 0, $month + 1));
                }
                $monthNamesLocalized[$locale] = $months;
                setlocale(LC_TIME, $currentLocale);
            }

            $dateStr = str_replace($monthNamesLocalized[$locale], $monthNamesLocalized['en_US'], $dateStr);
        }

        return date_parse($dateStr);
    }

    private function buildDateParts($dateStr)
    {
        $parts = [];

        if ('' === $dateStr) {
            $parts[] = $dateStr;

            return $parts;
        }

        if (!filter_var($dateStr, FILTER_VALIDATE_INT) === false) {
            $parts[] = (int) $dateStr;

            return $parts;
        }

        $date = $this->parseLocalizedDate($dateStr, 'de_DE');
        if (false === $date) {
            // failed
            $parts[] = $dateStr;

            return $parts;
        }

        foreach ([ 'year', 'month', 'day' ] as $key) {
            if (empty($date[$key])) {
                return $parts;
            }
            $parts[] = $date[$key];
        }

        return $parts;
    }

    /*
     * We transfer to Citeproc JSON
     * see https://github.com/citation-style-language/schema/blob/master/csl-data.json
     */
    public function jsonSerialize($locale = 'de_DE')
    {
        // see http://aurimasv.github.io/z2csl/typeMap.xml
        static $typeMap = [
            'audioRecording' => 'song',
            'blogPost' => 'post-weblog',
            'bookSection' => 'chapter',
            'document' => 'manuscript',
            'encyclopediaArticle' => 'entry-encyclopedia',
            'interview' => 'interview',
            'journalArticle' => 'article-journal',
            'letter' => 'personal_communication',
            'newspaperArticle' => 'article-newspaper',
            'presentation' => 'speech',
            'report' => 'report',
            'webpage' => 'webpage',
        ];

        $data = [
            'id' => $this->id,
            'citation-label' => $this->slug,
            'type' => array_key_exists($this->itemType, $typeMap)
                ? $typeMap[$this->itemType] : $this->itemType,
            'title' => self::adjustTitle($this->getName()),
            'container-title' => self::adjustTitle($this->containerName),
            'collection-title' => $this->series,
            'collection-number' => $this->seriesNumber,
            'volume' => $this->volume,
            'number-of-volumes' => $this->numberOfVolumes,
            'edition' => !is_null($this->bookEdition) && $this->bookEdition != 1
                ? $this->bookEdition : null,
            'publisher-place' => self::adjustPublisherPlace($this->publicationLocation, $locale),
            'publisher' => $this->publisher,
            'issued' => [
                "date-parts" => [ $this->buildDateParts($this->datePublished) ],
                "literal" => $this->datePublished,
            ],
            'page' => $this->pagination,
            'number-of-pages' => $this->numberOfPages,
            'DOI' => $this->doi,
            'ISBN' => $this->isbn,
            'ISSN' => $this->getIssn(),
            'URL' => $this->url,
            'accessed' => [
                "date-parts" => [ $this->buildDateParts($this->dateAccessed) ],
                "literal" => $this->dateAccessed,
            ],
            'language' => $this->language,
        ];

        if (!empty($this->creators)) {
            foreach ($this->creators as $creator) {
                // var_dump($creator);
                $key = $creator['creatorType'];
                if (!array_key_exists($key, $data)) {
                    $data[$key] = [];
                }

                $targetEntry = [];
                if (array_key_exists('name', $creator)) {
                    $targetEntry['family'] = $creator['name'];
                }
                else {
                    foreach ([ 'firstName' => 'given', 'lastName' => 'family'] as $src => $dst) {
                        if (array_key_exists($src, $creator)) {
                            $targetEntry[$dst] = $creator[$src];
                        }
                    }
                }

                $data[$key][] = $targetEntry;
            }
        }
        // var_dump($data);

        return $data;
    }

    public function jsonLdSerialize($locale, $omitContext = false)
    {
        // TODO:
        // for full property,
        // see https://www.worldcat.org/title/bauvertragsrecht-kommentar-zu-den-grundzugen-des-gesetzlichen-bauvertragsrechts-631-651-bgb-unter-besonderer-berucksichtigung-der-rechtsprechung-des-bundesgerichtshofs/oclc/920898066#microdatabox
        // and http://experiment.worldcat.org/entity/work/data/1348531819
        $type = 'CreativeWork';

        switch ($this->itemType) {
            case 'book':
                $type = 'Book';
                break;

            case 'journalArticle':
                $type = 'ScholarlyArticle';
                break;

            case 'bookSection':
            case 'encyclopediaArticle':
                $type = 'Chapter'; // see https://bib.schema.org/Chapter
                break;

            case 'newspaperArticle':
                $type = 'NewsArticle';
                break;

            case 'audioRecording':
                $type = 'AudioObject';
                break;

            case 'webpage':
                $type = 'WebPage';
                break;

            case 'letter':
            case 'document':
            case 'report':
            case 'interview':
            case 'presentation':
                $type = 'CreativeWork';
                break;

                // just for building isPartOf
            case 'issue':
                $type = 'PublicationIssue';
                break;
            case 'journal':
                $type = 'Periodical';
                break;
        }

        $ret = [
            '@context' => 'http://schema.org',
            '@type' => $type,
        ];

        if ($type == 'PublicationIssue') {
            // issues on't have a name, but might have an issue-number
            if (!empty($this->volume)) {
                $ret['issueNumber'] = $this->volume;
            }

            $parent = clone $this;
            $parent->setItemType('journal');
            $ret['isPartOf'] = $parent->jsonLdSerialize($parent);
        }
        else {
            $ret['name'] = $this->getName();
        }

        if ($omitContext) {
            unset($ret['@context']);
        }

        if (!empty($this->creators)) {
            $target = [];
            foreach ($this->creators as $creator) {
                if (array_key_exists('creatorType', $creator) && in_array($creator['creatorType'], [ 'author', 'editor', 'translator' ])) {
                    if ('author' == $creator['creatorType']
                        && in_array($type, [ 'PublicationIssue', 'Periodical' ])) {
                        continue;
                    }
                    else if ('editor' == $creator['creatorType'] && in_array($type, [ 'Chapter' ])) {
                        continue;
                    }

                    if (!empty($creator['firstName'])) {
                        // we have a person
                        $person = new Person();
                        if (!empty($creator['firstName'])) {
                            $person->setGivenName($creator['firstName']);
                        }

                        if (!empty($creator['lastName'])) {
                            $person->setFamilyName($creator['lastName']);
                        }

                        if (!array_key_exists($creator['creatorType'], $target)) {
                            $target[$creator['creatorType']] = [];
                        }

                        $target[$creator['creatorType']][] = $person->jsonLdSerialize($locale, true);
                    }
                }
            }

            foreach ($target as $key => $values) {
                $numValues = count($values);
                if (1 == $numValues) {
                    $ret[$key] = $values[0];
                }
                else if ($numValues > 1) {
                    $ret[$key] = $values;
                }
            }
        }

        if (in_array($type, [ 'Book', 'ScholarlyArticle', 'WebPage' ])) {
            foreach ([ 'url' ] as $property) {
                if (!empty($this->$property)) {
                    $ret[$property] = $this->$property;
                }
            }

            if (!empty($this->doi)) {
                $ret['sameAs'] = 'http://dx.doi.org/' . $this->doi;
            }
        }

        if (in_array($type, [ 'Book' ])) {
            $isbns = $this->getIsbnListNormalized(false);
            $numIsbns = count($isbns);

            if (1 == $numIsbns) {
                $ret['isbn'] = $isbns[0];
            }
            else if ($numIsbns > 1) {
                $ret['isbn'] = $isbns;
            }

            if (!empty($this->numberOfPages) && preg_match('/^\d+$/', $this->numberOfPages)) {
                $ret['numberOfPages'] = (int) $this->numberOfPages;
            }
        }
        else if (in_array($type, [ 'ScholarlyArticle', 'Chapter' ])) {
            foreach ([ 'pagination' ] as $property) {
                if (!empty($this->$property)) {
                    $ret[$property] = $this->$property;
                }
            }

            if (!empty($this->containerName)) {
                $parentItemType = null;
                switch ($type) {
                    case 'ScholarlyArticle':
                        $parentItemType = 'issue';
                        break;

                    case 'Chapter':
                        $parentItemType = 'book';
                        break;
                }

                if (!is_null($parentItemType)) {
                    $parent = clone $this;
                    $parent->setItemType($parentItemType);
                    $parent->setName($this->containerName);
                    if ('Chapter' == $type && !empty($this->creators)) {
                        $creatorsParent = [];
                        foreach ($this->creators as $creator) {
                            if (!in_array($creator['creatorType'], [ 'author', 'translator'])) {
                                $creatorsParent[] = $creator;
                            }
                        }
                        $parent->setCreators($creatorsParent);
                    }
                    $ret['isPartOf'] = $parent->jsonLdSerialize($locale, true);
                }
            }
        }

        if (in_array($type, [ 'Periodical', 'Book' ])) {
            foreach ([ 'issn' ] as $property) {
                if (!empty($this->$property)) {
                    $ret[$property] = $this->$property;
                }
            }

            if (!empty($this->publisher)) {
                $publisher = new Organization();
                $publisher->setName($this->publisher);
                $ret['publisher'] = $publisher->jsonLdSerialize($locale, true);
                if (!empty($this->publicationLocation)) {
                    $location = new Place();
                    $location->setName($this->publicationLocation);
                    $ret['publisher']['location'] = $location->jsonLdSerialize($locale, true);
                }
            }
        }

        if (!is_null($this->datePublished)
            && !in_array($type, [ 'ScholarlyArticle', 'Chapter', 'Periodical' ])) {
            $ret['datePublished'] = \App\Utils\JsonLd::formatDate8601($this->datePublished);
        }

        return $ret;
    }

    public function ogSerialize($locale, $baseUrl)
    {
        $type = null;

        switch ($this->itemType) {
            case 'book':
                $isbns = $this->getIsbnListNormalized(false);
                $type = 'books.book';
                break;
        }

        if (is_null($type)) {
            return;
        }

        $ret = [
            'og:type' => $type,
            'og:title' => $this->getName(),
        ];

        $isbns = $this->getIsbnListNormalized(false);
        if (empty($isbns)) {
            // 'books:isbn' is required
            return;
        }

        $ret['books:isbn'] = $isbns[0];

        return $ret;
    }

    public function twitterSerialize($locale, $baseUrl, $params = [])
    {
        $ret = [];

        $citation = $this->renderCitationAsHtml($params['citeProc'], $locale, true);
        if (preg_match('/(.*<span class="citeproc\-title">.*?<\/span>)(.*)/', $citation, $matches)) {
            $ret['twitter:title'] = rtrim(html_entity_decode(strip_tags($matches[1])));
            $ret['twitter:description'] = rtrim(html_entity_decode(strip_tags($matches[2])));
        }

        return $ret;
    }
}
