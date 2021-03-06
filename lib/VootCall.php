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

    public function makeCall($vootEndPoint, $bearerToken, &$attributes, $targetAttribute = "isMemberOf")
    {
        try {
            $bearerAuth = new \fkooman\Guzzle\Plugin\BearerAuth\BearerAuth($bearerToken);
            $this->httpClient->addSubscriber($bearerAuth);
            $response = $this->httpClient->get($vootEndPoint)->send();
            $jsonData = $response->getBody();
            $data = json_decode($jsonData, TRUE);

            // In VOOT1 the groups are in an 'entry' key, in VOOT2 they are one level higher
            if ( isset($data['entry'])) {
                $data = $data['entry'];
            }

            $groups = array();
            foreach ($data as $e) {
                $groups[] = $e['id'];
            }

            if (isset($attributes[$targetAttribute])) {
                $attributes[$targetAttribute] = array_merge($attributes[$targetAttribute], $groups);
            } else {
                $attributes[$targetAttribute] = $groups;
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
