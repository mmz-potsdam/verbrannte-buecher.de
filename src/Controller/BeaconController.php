<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/*
 * Simple pass-through proxy to fetch from non https site
 */
class BeaconController
extends BaseController
{
    private $client;
    private $beaconBaseUrl = 'http://beacon.findbuch.de'; // TODO: make configurable

    public function __construct(string $projectDir,
                                string $publicDir,
                                string $wordpressBaseUrl,
                                HttpClientInterface $client)
    {
        parent::__construct($projectDir, $publicDir, $wordpressBaseUrl);

        $this->client = $client;
    }

    /**
     * @Route("/helper/beacon{path}", name="beacon", requirements={"path"=".+"})
     */
    public function beaconAction(Request $request, $path)
    {
        $url = $this->beaconBaseUrl
             . $path
             . '?' . $request->server->get('QUERY_STRING');

        $clientResponse = $this->client->request('GET', $url);

        // Responses are lazy: this code is executed as soon as headers are received
        if (200 !== $clientResponse->getStatusCode()) {
            throw new \Exception($url . ' could not be fetched');
        }

        $contentType = $clientResponse->getHeaders()['content-type'][0];

        $response = new StreamedResponse();
        $response->headers->set('Content-Type', $contentType);

        $response->setCallback(function () use ($clientResponse) {
            foreach ($this->client->stream($clientResponse) as $chunk) {
                echo $chunk->getContent();
                flush();
            }
        });

        return $response;
    }
}
