<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

if (!array_key_exists('state', $_REQUEST)) {
        throw new SimpleSAML_Error_BadRequest('Missing required state query parameter.');
}

$di = new \fkooman\OAuth\Client\DiContainer();

$di['config'] = function() {
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
            "file" => "/Library/WebServer/Documents/frkonext/ssp/sp/data/php-oauth-client.log"
        ),
        "storage" => array(
            "dsn" => "sqlite:/Library/WebServer/Documents/frkonext/ssp/sp/data/client.sqlite",
            "persistentConnection" => false
        ),
    ));

    return $config;
};

$id = $_REQUEST['state'];
$state = SimpleSAML_Auth_State::loadState($id, 'vootgroups:authorize');

$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();

$service = new \fkooman\OAuth\Client\Callback($di);
$service->handleCallback($request);

SimpleSAML_Auth_ProcessingChain::resumeProcessing($state);
