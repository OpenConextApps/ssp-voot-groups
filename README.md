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
                'storage' => array(
                    'type' => 'SessionStorage'
                ), 
            ),            
        ),
    ),

If you want to use the PDO backed storage for using an SQL database you can 
modify the above storage configuration from:

    'storage' => array(
        'type' => 'SessionStorage'
    ), 

to this is you are using SQLite:

    'storage' => array(
        'type' => 'PdoStorage',
        'dsn' => 'sqlite:/var/simplesamlphp/data/oauth.sqlite',
        'username' => null,
        'password' => null
    ), 

Make sure this `oauth.sqlite` file is writable by the web server. This may 
involve setting file permissions, dealing with SELinux and possibly some web
server configuration.

See the [PDO documentation](http://www.php.net/manual/en/pdo.drivers.php) on 
how to use your favorite database. The database schema for storing the tokens 
can be found as part of the OAuth client and can be found in `schema/db.sql`. 
It was tested with SQLite and MySQL. Importing this schema and configuring the
database are out of scope here.

The schema can be found in `vendor/fkooman/php-oauth-client/schema/db.sql` 
after running Composer (see Installation section).

# Registration   
The OAuth configuration is shown above, but in addition you also need to 
register a `redirect_uri` at the OAuth 2.0 service. This depends on where
simpleSAMLphp is installed. For example:

    http://localhost/simplesaml/module.php/vootgroups/callback.php

This assumes that simpleSAMLphp is installed and reachable through 
`http://localhost/simplesaml`, modify the URL accordingly.
