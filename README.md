# Introduction
This is a module for [simpleSAMLphp](http://www.simplesamlphp.org) to fetch 
group memberships from an API service protected with OAuth 2.0 using the 
[VOOT](https://github.com/fkooman/voot-specification/blob/master/VOOT.md) 
protocol.

# Configuration
Below is an example configuration. You can place this in 
`metadata/saml20-idp-remote.php` for the IdP you want to attach the group
fetching to.

    'authproc' => array(
        40 => array (
            'class' => 'vootgroups:AttributeAddVootGroups',
            'vootEndpoint' => 'http://localhost/frkonext/php-voot-proxy/voot.php/groups/@me',
            'clientConfig' => array(
                'authorize_endpoint' => 'http://localhost/frkonext/php-oauth/authorize.php',
                'client_id' => 'foo',
                'client_secret' => 'foobar',
                'token_endpoint' => 'http://localhost/frkonext/php-oauth/token.php' 
            ),            
        ),
    ),

This should be enough to get going.
