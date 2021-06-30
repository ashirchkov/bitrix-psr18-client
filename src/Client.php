<?php

namespace Http\Adapter\Bitrix;

use Bitrix\Main\Web\HttpClient as BitrixHttpClient;
use Http\Client\HttpClient;
use Http\Discovery\Psr17FactoryDiscovery;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Client implements HttpClient
{

    private $options;
    private $streamFactory;
    private $responseFactory;

    public function __construct(array $options = []) {

        $this->streamFactory = Psr17FactoryDiscovery::findStreamFactory();
        $this->responseFactory = Psr17FactoryDiscovery::findResponseFactory();

    }

    public function sendRequest(RequestInterface $request): ResponseInterface {

        $client = new BitrixHttpClient($this->options);

        foreach($request->getHeaders() as $headerName => $headerValues) {
            if(count($headerValues) > 1) {
                foreach($headerValues as $headerValue) {
                    $client->setHeader($headerName, $headerValue, false);
                }
            } else {
                $client->setHeader($headerName, current($headerValues));
            }
        }

        $queryResult = $client->query(
            $request->getMethod(),
            $request->getUri()->__toString(),
            $request->getBody()->__toString()
        );

        $response = $this->responseFactory->createResponse(
            $client->getStatus()
        );

        if($queryResult) {

            $body = $this->streamFactory->createStream(
                $client->getResult()
            );

            $response = $response->withBody($body);

            foreach($client->getHeaders()->toArray() as $header) {
                if(count($header['values']) > 1) {
                    foreach($header['values'] as $headerValue) {
                        $response = $response->withAddedHeader($header['name'], $headerValue);
                    }
                } else {
                    $response = $response->withHeader($header['name'], current($header['values']));
                }
            }

        }

        return $response;

    }

}