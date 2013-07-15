<?php

class sspmod_vootgroups_SspDiContainer extends \Pimple
{
    public function __construct()
    {
        $this['clientConfig'] = function() {
            return \fkooman\OAuth\Client\ClientConfig::fromArray(array(
                "authorize_endpoint" => "http://localhost/frkonext/php-oauth/authorize.php",
                "client_id" => "foo",
                "client_secret" => "foobar",
                "token_endpoint" => "http://localhost/frkonext/php-oauth/token.php"
            ));
        };

        $this['vootEndpoint'] = "http://localhost/frkonext/php-voot-proxy/voot.php/groups/@me";

        $this['httpClient'] = function() {
            return new \Guzzle\Http\Client();
        };

        $this['storage'] = function() {
            return new \fkooman\OAuth\Client\SessionStorage();
        };
    }

}
