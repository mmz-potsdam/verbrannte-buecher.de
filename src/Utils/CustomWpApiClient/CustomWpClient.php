<?php

namespace App\Utils\CustomWpApiClient;

use RuntimeException;

class CustomWpClient extends \Vnn\WpApiClient\WpClient
{
    /**
     * @var array
     */
    private $endPoints = [];

    /**
     * @param $endpoint
     * @param array $args
     * @return Endpoint\AbstractWpEndpoint
     */
    public function __call($endpoint, array $args)
    {
        if (!isset($this->endPoints[$endpoint])) {
            $class = 'Vnn\WpApiClient\Endpoint\\' . ucfirst($endpoint);
            if (class_exists($class)) {
                $this->endPoints[$endpoint] = new $class($this);
            }
            else {
                // look for custom endpoint
                $class = 'App\Utils\CustomWpApiClient\Endpoint\\' . ucfirst($endpoint);

                if (class_exists($class)) {
                    $this->endPoints[$endpoint] = new $class($this);
                }
                else {
                    throw new RuntimeException('Endpoint "' . $endpoint . '" does not exist"');
                }
            }
        }

        return $this->endPoints[$endpoint];
    }
}
