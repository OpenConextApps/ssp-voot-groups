<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

if (!array_key_exists('state', $_REQUEST)) {
        throw new SimpleSAML_Error_BadRequest('Missing required state query parameter.');
}

$di = new sspmod_vootgroups_SspDiContainer();

$id = $_REQUEST['state'];
$state = SimpleSAML_Auth_State::loadState($id, 'vootgroups:authorize');

$cb = new \fkooman\OAuth\Client\Callback();
$accessToken = $cb->handleCallback($_GET);

// obtain attributes from state
$attributes =& $state['Attributes'];

$vootCall = new sspmod_vootgroups_VootCall($di);
if (false === $vootCall->makeCall($accessToken->getAccessToken(), $attributes)) {
    // unable to fetch groups, something is wrong with the token?
    die("unable to fetch groups with seemingly valid bearer token");
}

// FIXME: the resumeProcessing does not work yet... how do you deal with this?!
SimpleSAML_Auth_ProcessingChain::resumeProcessing($state);
