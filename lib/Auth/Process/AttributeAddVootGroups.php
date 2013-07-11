<?php

/**
 * Filter to add group membership to attributes from VOOT provider.
 *
 * This filter allows you to add attributes to the attribute set being processed.
 *
 * @author François Kooman, SURFnet
 * @package simpleSAMLphp
 * @version $Id$
 */
class sspmod_vootGroups_Auth_Process_AttributeAddVootGroups extends SimpleSAML_Auth_ProcessingFilter
{
    /**
     * The VOOT endpoint to query for group membership
     */
    private $vootEndpoint;

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
     * @param array &$request The current request
     */
    public function process(&$request)
    {
        assert('is_array($request)');
        assert('array_key_exists("Attributes", $request)');

        $attributes =& $request['Attributes'];

        require_once '/Library/WebServer/Documents/frkonext/php-oauth-client/vendor/autoload.php';
        $client = new \fkooman\OAuth\Client\Api("php-voot-client", $attributes['uid'][0], array("http://openvoot.org/groups"));
        $client->setReturnUri("http://localhost/frkonext/saml/");

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
