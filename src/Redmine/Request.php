<?php

namespace Atipik\RedmineReminder\Redmine;

class Request {
    protected $baseUri;
    protected $apiKey;

    public function __construct($baseUri, $apiKey)
    {
        $this->baseUri = rtrim($baseUri, '/');
        $this->apiKey  = $apiKey;

        $this->client = new \GuzzleHttp\Client();
    }

    public function all($modelName, array $conditions = array())
    {
        return $this->getData('GET', strtolower($modelName) . '.json', $conditions);
    }

    public function get($modelName, $id, array $conditions = array())
    {
        return $this->getData('GET', strtolower($modelName) . '/' . $id . '.json', $conditions);
    }

    protected function getData($method, $uri, array $conditions = array())
    {
        $method = strtolower($method);

        $conditions['limit'] = 9999;

        $response = $this->client->$method(
            $this->baseUri . '/' . ltrim($uri, '/'),
            array(
                'auth'    => array($this->apiKey, $this->apiKey),
                'query'   => $conditions,
                'headers' => array(
                    'Content-Type' => 'application/json'
                ),
            )
        );

        $data = $response->json();

        return array_shift($data);
    }
}