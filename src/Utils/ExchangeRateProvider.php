<?php

namespace App\Utils;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ExchangeRateProvider
{
    /** @var HttpClientInterface */
    private $client;

    /** @var string */
    private $apiKey;

    public function __construct(HttpClientInterface $client, string $apiKey)
    {
        $this->client = $client;
        $this->apiKey = $apiKey;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function getRate(string $from, string $to): float
    {
        $response = $this->client->request(
            'GET',
            "https://api.exchangeratesapi.io/v1/latest?access_key={$this->apiKey}&symbols={$to}&base={$from}"
        );
        $data = $response->toArray();
        return $data['rates'][$to];
    }
}
