<?php

class sspmod_vootgroups_SspDiContainer extends \Pimple
{
    public function __construct()
    {
        parent::__construct();

        $this->di['clientConfig'] = function() {
            return \fkooman\OAuth\Client\ClientConfig::fromArray(array(
                "authorize_endpoint" => "http://localhost/oauth/php-oauth/authorize.php",
                "client_id" => "foo",
                "client_secret" => "foobar",
                "token_endpoint" => "http://localhost/oauth/php-oauth/token.php"
            ));
        };

        $this->di['apiEndpoint'] = "http://localhost/frkonext/php-voot-proxy/voot.php";

        $this->di['httpClient'] = function() {
            return new \Guzzle\Http\Client();
        };

        $this->di['storage'] = function() {
            return new \fkooman\OAuth\Client\SessionStorage();
        };
    }

}
