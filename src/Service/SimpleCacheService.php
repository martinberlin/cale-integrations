<?php
namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;

class SimpleCacheService
{
    private $config;
    private $httpClient;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->httpClient = HttpClient::create();
    }

    public function request(string $method, $url, array $options) {

        if ($this->config['enabled'] === 1) {
            // Check if there is a cache hit in our DB

            // If not:
            $response = $this->httpClient->request($method, $url, $options);
        } else {
        // Cache is disabled, make the request directly
        $response = $this->httpClient->request($method, $url, $options);
        }

        return $response;
    }

}