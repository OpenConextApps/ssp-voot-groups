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

    private $vootEndpoint;

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

        $this->di = new \fkooman\OAuth\Client\DiContainer();

        $this->di['config'] = function() {
            $config = new \fkooman\Config\Config(array(
                "registration" => array(
                    "php-voot-client" => array(
                        "authorize_endpoint" => "http://localhost/frkonext/php-oauth/authorize.php",
                        "client_id" => "php-voot-client",
                        "client_secret" => "f00b4r",
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

        if (!isset($config['vootEndpoint'])) {
            throw new Exception('vootEndpoint configuration option not set');
        }
        $this->vootEndpoint = $config['vootEndpoint'];

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
        $client->setCallbackId("php-voot-client");
        $client->setUserId($attributes['uid'][0]);
        $client->setScope(array("http://openvoot.org/groups"));

        //die($this->di['config']->s("storage")->l("dsn"));

        $accessToken = $client->getAccessToken();
        if (FALSE === $accessToken) {
            // we don't have an access token, get a new one

            // do some state stuff
            $client->setReturnUri("http://www.example.org");
            $id = SimpleSAML_Auth_State::saveState($state, 'vootgroups:authorize');

            $client->setState($id);
            $returnUri = $client->getAuthorizeUri();

            //$url = SimpleSAML_Module::getModuleURL('ssp-voot-groups/callback.php');
            SimpleSAML_Utilities::redirect($returnUri);
        }
        // try the request, if it fails mark token as invalid and try again

        //$client->setReturnUri("http://localhost/frkonext/saml/");

        $response = $client->makeRequest($this->vootEndpoint);
        //$jsonData = file_get_contents($this->vootEndpoint);
        $jsonData = $response->getBody();
        $data = json_decode($jsonData, TRUE);
        $groups = array();
        foreach ($data['entry'] as $e) {
            $groups[] = $e['id'];
        }

        if (isset($attributes['groups'])) {
            $attributes['groups'] = array_merge($attributes['groups'], $groups);
        } else {
            $attributes['groups'] = $groups;
        }
    }

}
