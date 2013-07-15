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
            die($e->getMessage());
            // something was wrong with the Bearer token...
            return false;
        } catch (\Guzzle\Http\Exception\BadResponseException $e) {

            die($e->getMessage());
            // something was wrong with the request...
            return false;
        }
    }
}
