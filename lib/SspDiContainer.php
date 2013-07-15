<?php

class sspmod_vootgroups_SspDiContainer extends \Pimple
{
    public function __construct($config)
    {
        $this['clientConfig'] = function() use ($config) {
            return \fkooman\OAuth\Client\ClientConfig::fromArray($config['clientConfig']);
        };

        $this['vootEndpoint'] = function() use ($config) {
            return $config['vootEndpoint'];
        };

        $this['storage'] = function() use ($config) {
            return new \fkooman\OAuth\Client\SessionStorage();
        };
    }

}
