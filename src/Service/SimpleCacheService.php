<?php
namespace App\Service;

use App\Entity\SimpleCache;
use App\Repository\SimpleCacheRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;

class SimpleCacheService
{
    private $config;
    private $httpClient;
    private $cacheRepository;
    private $em;

    public function __construct(array $config, SimpleCacheRepository $cacheRepository, EntityManagerInterface $entityManager)
    {
        $this->config = $config;
        $this->httpClient = HttpClient::create();
        $this->cacheRepository = $cacheRepository;
        $this->em = $entityManager;
    }

    /**
     * @param string $method
     * @param $url
     * @param array $options
     * @param string $int_api_id
     * @param int $ttl
     * @return Response|\Symfony\Contracts\HttpClient\ResponseInterface - Note in cases where a new HttpClient request is made returns that response type
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function request(string $method, $url, array $options, string $int_api_id, int $ttl = 0) {

        if ($this->config['enabled'] === 1) {
            // Check if there is a cache hit in our DB
            $ttl_seconds = (!$ttl) ? $this->config['ttl_seconds'] : $ttl;
            $urlSha = hash($this->config['hash_algo'], $url);

            $queryCache = $this->cacheRepository->findOneBy([
                'intApiId' => $int_api_id,
                'url'      => $urlSha
            ]);
            $response = new Response();

            // Entry found: Check ttl_seconds
            if ($queryCache instanceof SimpleCache && (time()-$queryCache->getCreated() <= $ttl_seconds)) {
                $queryCache->incrHits();
                $this->em->persist($queryCache);
                $this->em->flush();
                $response->setStatusCode(200);
                $response->setContent($queryCache->getResponseContent());
                return $response;
            } else {
                // If there is a stale expired cache entry clean it up before since there is an unique index in intapi_id/url
                if ($queryCache instanceof SimpleCache) {
                    $this->em->remove($queryCache);
                    $this->em->flush();
                }
                // Query API and leave last response contents in a SimpleCache entry
                $response = $this->httpClient->request($method, $url, $options);
                $cache = new SimpleCache();
                $cache->setIntApiId($int_api_id);
                $cache->setUrl($urlSha);
                $cache->setResponseContent($response->getContent());
                $cache->setResponseStatus($response->getStatusCode());
                $this->em->persist($cache);
                $this->em->flush();
            }

        } else {
        // Cache is disabled, make the request directly
        $response = $this->httpClient->request($method, $url, $options);
        }

        return $response;
    }

}