<?php

namespace App\Entity;

/**
 * Shared method for Schema.org entities
 *
 */
abstract class SchemaOrg
{
    public static function formatDateIncomplete($dateStr)
    {
        if (preg_match('/^\d{4}$/', $dateStr)) {
            $dateStr .= '-00-00';
        }
        else if (preg_match('/^\d{4}\-\d{2}$/', $dateStr)) {
            $dateStr .= '-00';
        }
        else if (preg_match('/^(\d+)\.(\d+)\.(\d{4})$/', $dateStr, $matches)) {
            $dateStr = join('-', [ $matches[3], $matches[2], $matches[1] ]);
        }

        return $dateStr;
    }

    public static function stripAt($name)
    {
        return preg_replace('/(\s+)@/', '\1', $name);
    }

    public static function xmlSpecialchars($txt)
    {
        return htmlspecialchars($txt, ENT_XML1, 'UTF-8');
    }

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var int
     */
    protected $status;

    /**
     * @var string URL of the item.
     */
    protected $url;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $changedAt;

    /**
     * Sets id.
     *
     * @param string $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Gets id.
     *
     * @return string|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets status.
     *
     * @param int $status
     *
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Gets status.
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Sets url.
     *
     * @param string $url
     *
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Gets url.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Sets name.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Gets name.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets createdAt
     *
     * @param string|\DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        if (is_object($createdAt)) {
            $this->createdAt = $createdAt;
        }

        if (!empty($createdAt)) {
            $this->createdAt = \DateTime::createFromFormat(\DateTime::ISO8601, $createdAt);
        }

        return $this;
    }

    /**
     * Gets createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Sets changedAt.
     *
     * @param string|\DateTime $changedAt
     *
     * @return $this
     */
    public function setChangedAt($changedAt)
    {
        if (is_object($changedAt)) {
            $this->changedAt = $changedAt;
        }

        if (!empty($changedAt)) {
            $this->changedAt = \DateTime::createFromFormat(\DateTime::ISO8601, $changedAt);
        }

        return $this;
    }

    /**
     * Gets changedAt.
     *
     * @return \DateTime
     */
    public function getChangedAt()
    {
        return $this->changedAt;
    }
}
