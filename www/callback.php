<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

if (!array_key_exists('state', $_REQUEST)) {
        throw new SimpleSAML_Error_BadRequest('Missing required state query parameter.');
}

$id = $_REQUEST['state'];
$state = SimpleSAML_Auth_State::loadState($id, 'vootgroups:authorize');

$config = $state['vootgroups:config'];
$diContainer = new sspmod_vootgroups_SspDiContainer($config);

$cb = new \fkooman\OAuth\Client\Callback();
$cb->setClientConfig("foo", $diContainer['clientConfig']);
$cb->setStorage($diContainer['storage']);
$cb->setHttpClient(new \Guzzle\Http\Client());

$accessToken = $cb->handleCallback($_GET);

// obtain attributes from state
$attributes =& $state['Attributes'];

$vootCall = new sspmod_vootgroups_VootCall();
$vootCall->setHttpClient(new \Guzzle\Http\Client());

if (false === $vootCall->makeCall($diContainer['vootEndpoint'], $accessToken->getAccessToken(), $attributes)) {
    // unable to fetch groups, something is wrong with the token?
    throw new Exception("unable to fetch groups with seemingly valid bearer token");
}

// FIXME: the resumeProcessing does not work yet... how do you deal with this?!
SimpleSAML_Auth_ProcessingChain::resumeProcessing($state);
