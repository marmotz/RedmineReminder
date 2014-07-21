<?php

namespace Trucker\Requests;

use Trucker\Facades\Config as TruckerConfig;

class JsonRestRequest extends RestRequest
{
    /**
     * {@inheritdoc}
     */
    public function createRequest($baseUri, $path, $httpMethod = 'GET', $requestHeaders = array(), $httpMethodParam = null)
    {
        TruckerConfig::set('resource.collection_key', trim($path, '/'));

        return parent::createRequest($baseUri, $path . '.json', $httpMethod, $requestHeaders, $httpMethodParam);
    }
}