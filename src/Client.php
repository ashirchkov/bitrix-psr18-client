<?php

namespace Http\Adapter\Bitrix;

use Bitrix\Main\Web\HttpClient as BitrixHttpClient;
use Http\Client\HttpClient;
use Http\Discovery\Psr17FactoryDiscovery;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Client implements HttpClient
{

    private $client;
    private $streamFactory;
    private $responseFactory;

    public function __construct(array $options = []) {


        $this->client = new BitrixHttpClient($options);
        $this->streamFactory = Psr17FactoryDiscovery::findStreamFactory();
        $this->responseFactory = Psr17FactoryDiscovery::findResponseFactory();

    }

    public function sendRequest(RequestInterface $request): ResponseInterface {

        $queryResult = $this->client->query(
            $request->getMethod(),
            $request->getUri(),
            $request->getBody()
        );

        $response = $this->responseFactory->createResponse(
            $this->client->getStatus()
        );

        if($queryResult) {

            $body = $this->streamFactory->createStream(
                $this->client->getResult()
            );

            $response->withBody($body);

        }

        return $response;

    }

}