<?php

require_once dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php';

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
    private $di;

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
        $this->di = new sspmod_vootgroups_SspDiContainer();
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

        $client = new \fkooman\OAuth\Client\Api();
        $client->setDiContainer($this->di);
        $client->setCallbackId("foo");
        $client->setUserId($attributes['uid'][0]);
        $client->setScope(array("http://openvoot.org/groups"));

        $accessToken = $client->getAccessToken();
        if (false === $accessToken) {
            // we don't have an access token, get a new one
            $client->setReturnUri("http://www.example.org");
            $id = SimpleSAML_Auth_State::saveState($state, 'vootgroups:authorize');
            $client->setState($id);
            SimpleSAML_Utilities::redirect($client->getAuthorizeUri());
        } else {
            $vootCall = new sspmod_vootgroups_VootCall($this->di);
            $groups = $vootCall->makeCall($accessToken->getToken()->getAccessToken(), $attributes);
            if (false === $groups) {
                // unable to fetch groups, something is wrong with the token?
                die("unable to fetch groups with seemingly valid bearer token");
            }
        }
    }
}
