<?php

namespace App\Utils\CustomWpApiClient\Endpoint;

/**
 * Class Events
 */
class Events extends \Vnn\WpApiClient\Endpoint\AbstractWpEndpoint
{
    /**
     * {@inheritdoc}
     */
    protected function getEndpoint()
    {
        return '/wp-json/wp/v2/events';
    }
}
