<?php

class sspmod_vootgroups_SspDiContainer extends \fkooman\OAuth\Client\DiContainer
{
    public function __construct()
    {
        parent::__construct();

        $this->di['config'] = function() {
            $config = new \fkooman\Config\Config(array(
                "registration" => array(
                    "foo" => array(
                        "authorize_endpoint" => "http://localhost/frkonext/php-oauth/authorize.php",
                        "client_id" => "foo",
                        "client_secret" => "foobar",
                        "token_endpoint" => "http://localhost/frkonext/php-oauth/token.php"
                    ),
                ),
                "log" => array(
                    "level" => 100,
                    "file" => "/var/www/html/frkonext/ssp/sp/data/php-oauth-client.log"
                ),
                "storage" => array(
                    "dsn" => "sqlite:/var/www/html/frkonext/ssp/sp/data/client.sqlite",
                    "persistentConnection" => false
                ),
                "vootEndpoint" => "http://localhost/frkonext/php-voot-proxy/voot.php",
            ));

            return $config;
        };

    }

}
