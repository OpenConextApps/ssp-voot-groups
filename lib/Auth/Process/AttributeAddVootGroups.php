<?php

require_once dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php';

use fkooman\Guzzle\Plugin\BearerAuth\BearerAuth;
use fkooman\Guzzle\Plugin\BearerAuth\Exception\BearerErrorResponseException;
use Guzzle\Http\Exception\BadResponseException;

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
        $client->setCallbackId("foo");
        $client->setUserId($attributes['uid'][0]);
        $client->setScope(array("http://openvoot.org/groups"));

        $accessToken = $client->getAccessToken();
        if (FALSE === $accessToken) {
            // we don't have an access token, get a new one
            $client->setReturnUri("http://www.example.org");
            $id = SimpleSAML_Auth_State::saveState($state, 'vootgroups:authorize');
            $client->setState($id);
            SimpleSAML_Utilities::redirect($client->getAuthorizeUri());
        }

//        try {
            $bearerAuth = new BearerAuth($accessToken->getToken()->getAccessToken());
            $this->di['http']->addSubscriber($bearerAuth);
            $response = $this->di['http']->get($this->vootEndpoint)->send();
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
//         } catch (BearerErrorResponseException $e) {
//             die($e->getMessage());
//         } catch (BadResponseException $e) {
//             die($e->getMessage());
//         }
    }

}
