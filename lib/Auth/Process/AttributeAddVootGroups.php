<?php

// Conditionally include composer autoload file;
// we don't need it if installed with SSP's composer-module-installer.
if ( file_exists(dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php') ) {
    require_once dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php';
}

/**
 * Filter to add group membership to attributes from VOOT provider.
 *
 * This filter allows you to add attributes to the attribute set being processed.
 *
 * @author François Kooman, SURFnet
 * @package simpleSAMLphp
 * @version $Id$
 */
class sspmod_vootgroups_Auth_Process_AttributeAddVootGroups extends SimpleSAML_Auth_ProcessingFilter
{
    /** @var \Pimple */
    private $diContainer;

    private $config;

    /**
     * Initialize this filter.
     *
     * @param array $config   Configuration information about this filter.
     * @param mixed $reserved For future use.
     */
    public function __construct($config, $reserved)
    {
        parent::__construct($config, $reserved);
        assert('is_array($config)');
        $this->config = $config;
        $this->diContainer = new sspmod_vootgroups_SspDiContainer($config);
    }

    /**
     * Apply filter to add or replace attributes.
     *
     * Add or replace existing attributes with the configured values.
     *
     * @param array &$state The current request
     */
    public function process(&$state)
    {
        assert('is_array($state)');
        assert('array_key_exists("Attributes", $state)');

        $attributes =& $state['Attributes'];

        $state['vootgroups:config'] = $this->config;

        $client = new \fkooman\OAuth\Client\Api("ssp-voot-groups", $this->diContainer['clientConfig'], $this->diContainer['storage'], new \Guzzle\Http\Client());
        $userIdAttribute = $this->diContainer['userIdAttribute'];
        $context = new \fkooman\OAuth\Client\Context($attributes[$userIdAttribute][0], array($this->diContainer['vootScope']));

        $this->getTokenAndGroups($client, $context, $state);
    }

    private function getTokenAndGroups(\fkooman\OAuth\Client\Api $api, \fkooman\OAuth\Client\Context $context, &$state)
    {
        $attributes =& $state['Attributes'];

        $accessToken = $api->getAccessToken($context);
        if (false === $accessToken) {
            // we don't have an access token, get a new one
            $id = SimpleSAML_Auth_State::saveState($state, 'vootgroups:authorize');
            SimpleSAML_Utilities::redirect($api->getAuthorizeUri($context, $id));
        } else {
            $vootCall = new sspmod_vootgroups_VootCall();
            $vootCall->setHttpClient(new \Guzzle\Http\Client());
            if (false === $vootCall->makeCall($this->diContainer['vootEndpoint'], $accessToken->getAccessToken($context), $attributes, $this->diContainer['targetAttribute'])) {
                // the token was not accepted, delete it
                $api->deleteAccessToken($context);
                // after the token is deleted we get an access token again and
                // try again
                // FIXME: loop detection? but how to implement this...?
                $this->getTokenAndGroups($api, $context, $state);
            }
        }
    }

}
