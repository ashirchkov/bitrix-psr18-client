<?php

namespace Http\Client\Bitrix;

use Bitrix\Main\Web\HttpClient;
use Http\Discovery\Psr17FactoryDiscovery;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Client implements ClientInterface
{

    private $client;
    private $streamFactory;
    private $responseFactory;

    public function __construct(array $options = []) {

        $this->client = new HttpClient($options);
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