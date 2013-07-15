# Introduction
This is a module for [simpleSAMLphp](http://www.simplesamlphp.org) to fetch 
group memberships from an API service protected with OAuth 2.0 using the 
[VOOT](https://github.com/fkooman/voot-specification/blob/master/VOOT.md) 
protocol.

# Installation
You can install this module in the `modules` directory of simpleSAMLphp. We 
assume you installed simpleSAMLphp in `/var/simplesamlphp`:

    cd /var/simplesamlphp/modules
    git clone https://github.com/fkooman/ssp-voot-groups.git vootgroups

To enable the module:

    touch /var/simplesamlphp/modules/vootgroups/enable

Now you have to install some dependencies using
[Composer](http://www.getcomposer.org). There is no external code bundled in 
the code of this simpleSAMLphp module.

    cd /var/simplesamlphp/modules/vootgroups
    php /path/to/composer.phar install

That should be all for the installation.

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

Above, the OAuth configuration is shown, but in addition you also need to 
register a `redirect_uri` at the OAuth 2.0 service. This depends on where
simpleSAMLphp is installed. For example:

    http://localhost/simplesaml/module.php/vootgroups/callback.php

This assumes that simpleSAMLphp is installed at `http://localhost/simplesaml`,
modify the URL accordingly.
