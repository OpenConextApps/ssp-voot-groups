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

        $client = new \fkooman\OAuth\Client\Api();

        $client->setClientConfig("foo", $this->diContainer['clientConfig']);
        $client->setStorage($this->diContainer['storage']);
        $client->setHttpClient(new \Guzzle\Http\Client());

        $client->setUserId($attributes['uid'][0]);
        $client->setScope(array("http://openvoot.org/groups"));

        $accessToken = $client->getAccessToken();
        if (false === $accessToken) {
            // we don't have an access token, get a new one
            $id = SimpleSAML_Auth_State::saveState($state, 'vootgroups:authorize');
            $client->setState($id);
            SimpleSAML_Utilities::redirect($client->getAuthorizeUri());
        } else {
            $vootCall = new sspmod_vootgroups_VootCall();
            $vootCall->setHttpClient(new \Guzzle\Http\Client());
            if (false === $vootCall->makeCall($accessToken->getAccessToken(), $attributes)) {
                // unable to fetch groups, something is wrong with the token?
                throw new Exception("unable to fetch groups with seemingly valid bearer token");
            }
        }
    }
}
