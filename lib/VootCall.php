<?php

class sspmod_vootgroups_VootCall
{
    private $httpClient;

    public function __construct()
    {
        $this->httpClient = null;
    }

    public function setHttpClient(\Guzzle\Http\Client $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function makeCall($vootEndPoint, $bearerToken, &$attributes)
    {
        try {
            $bearerAuth = new \fkooman\Guzzle\Plugin\BearerAuth\BearerAuth($bearerToken);
            $this->httpClient->addSubscriber($bearerAuth);
            $response = $this->httpClient->get($vootEndPoint)->send();
            $jsonData = $response->getBody();
            $data = json_decode($jsonData, TRUE);
            $groups = array();
            foreach ($data['entry'] as $e) {
                $groups[] = $e['id'];
            }

            if (isset($attributes['groups'])) {
                $attributes['groups'] = array_merge($attributes['groups'], $groups);
            } else {
                $attributes['groups'] = $groups;
            }

            return true;
        } catch (\fkooman\Guzzle\Plugin\BearerAuth\Exception\BearerErrorResponseException $e) {
            if ("invalid_token" === $e->getBearerReason()) {
                // the token we used was invalid, possibly revoked, we throw it away
                return false;
            }
            throw $e;
        }
    }
}
