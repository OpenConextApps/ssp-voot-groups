<?php

class sspmod_vootgroups_VootCall
{
    /** @var \Pimple */
    private $p;

    public function __construct(\Pimple $p)
    {
        $this->p = $p;
    }

    public function makeCall($bearerToken, &$attributes)
    {
        try {
            $bearerAuth = new \fkooman\Guzzle\Plugin\BearerAuth\BearerAuth($bearerToken);
            $this->p['httpClient']->addSubscriber($bearerAuth);
            $response = $this->p['httpClient']->get($this->p['vootEndpoint'])->send();
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
            // something was wrong with the Bearer token...
            return false;
        } catch (\Guzzle\Http\Exception\BadResponseException $e) {
            // something was wrong with the request...
            return false;
        }
    }
}
