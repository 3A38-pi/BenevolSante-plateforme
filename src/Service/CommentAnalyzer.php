<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class CommentAnalyzer
{
    private $httpClient;
    private $apiUser;
    private $apiSecret;

    public function __construct(HttpClientInterface $httpClient, string $apiUser='1608670126', string $apiSecret='Az9aNCSbfTmc56AZNQbnjWzTMcbx9WgN')
    {
        $this->httpClient = $httpClient;
        $this->apiUser = $apiUser;
        $this->apiSecret = $apiSecret;
    }
    public function analyzeComment(string $text): ?array
    {
        try {
            $response = $this->httpClient->request('POST', 'https://api.sightengine.com/1.0/text/check.json', [
                'body' => [
                    'text' => $text,
                    'lang' => 'en',
                    'categories' => 'profanity,personal,link,drug,weapon,spam,content-trade,money-transaction,extremism,violence,self-harm,medical',
                    'mode' => 'rules',
                    'api_user' => $this->apiUser,
                    'api_secret' => $this->apiSecret
                ]
            ]);
    
            return $response->toArray();
        } catch (\Exception $e) {
            return ['error' => 'Erreur API : ' . $e->getMessage()];
        }
    }
    

}
