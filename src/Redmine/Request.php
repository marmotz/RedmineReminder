<?php

namespace Atipik\RedmineReminder\Redmine;

class Request {
    protected $baseUri;
    protected $apiKey;

    public function __construct($baseUri, $apiKey)
    {
        $this->baseUri = rtrim($baseUri, '/');
        $this->apiKey  = $apiKey;
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

        $url = sprintf(
            'http://%s:%s@%s/%s?%s',
            $this->apiKey,
            $this->apiKey,
            str_replace('http://', '', $this->baseUri),
            ltrim($uri, '/'),
            http_build_query($conditions)
        );

        $response = file_get_contents($url);

        $data = json_decode($response, true);

        return array_shift($data);
    }
}
