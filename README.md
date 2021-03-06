# Introduction
This is a module for [simpleSAMLphp](http://www.simplesamlphp.org) to fetch 
group memberships from an API service protected with OAuth 2.0 using the 
[VOOT](http://openvoot.org)
protocol (versions 1 and 2 are supported) and add them to the list of
attributes received from the identity provider.

![ssp-voot-groups](https://github.com/OpenConextApps/ssp-voot-groups/raw/master/docs/ssp-voot-groups.png)

# Why?
Because it is cumbersome to implement your own OAuth 2.0 and REST API client to 
fetch group memberships while they could also be made part of the received 
attributes when you are already a SAML service provider.

# Who?
If you are a service provider that connects to an identity federation that 
supports VOOT to publish group membership information for users logging into 
your service. If you are currently already using simpleSAMLphp as SAML SP 
software you can just install the module. If you are using other software you
can also install a simpleSAMLphp SAML proxy and install the module.

# Installation
This module can be installed in two ways:
 1. By unpacking a [release tarball](https://github.com/OpenConextApps/ssp-voot-groups/releases) under the `modules/` directory; or
 2. with the [simpleSAMLphp module installer](https://simplesamlphp.org/modules).

For the first option, download `ssp-voot-groups.`_x.y.z_`.tar.gz` and unpack it under your `modules/` directory of simpleSAMLphp. For the second option, you need to have [Composer](https://getcomposer.org/). Then it should
suffice to run:

    composer.phar require openconextapps/simplesamlphp-module-vootgroups

# Configuration
Below is an example configuration for VOOT 1.0. You can place this in 
`metadata/saml20-idp-remote.php` for the IdP you want to attach the group
fetching to.

    'authproc' => array(
        40 => array (
            'class' => 'vootgroups:AttributeAddVootGroups',
            'vootScope' => 'http://openvoot.org/groups',
            'vootEndpoint' => 'https://voot.example.org/groups/@me',
            'userIdAttribute' => 'uid',
            'targetAttribute' => 'isMemberOf',
            'clientConfig' => array (
                'authorize_endpoint' => 'https://auth.example.org/authorize',
                'client_id' => 'my_client_id',
                'client_secret' => 'my_client_secret',
                'token_endpoint' => 'https://auth.example.org/token',
            ),
            'storage' => array (
                'type' => 'SessionStorage',
            ),
        ),
    ),

For VOOT 2.0, use `/me/groups` as the `vootEndpoint`.

If you want to use the PDO backed storage for using an SQL database you can 
modify the above storage configuration from:

    'storage' => array (
        'type' => 'SessionStorage',
    ),

to this is you are using SQLite:

    'storage' => array(
        'type' => 'PdoStorage',
        'dsn' => 'sqlite:/var/simplesamlphp/data/oauth.sqlite',
    ),

Make sure this `oauth.sqlite` file is writable by the web server. This may 
involve setting file permissions, dealing with SELinux and possibly some web
server configuration. If you are using MySQL you could use the following:

    'storage' => array(
        'type' => 'PdoStorage',
        'dsn' => 'mysql:host=localhost;dbname=oauth',
        'username' => 'foo',
        'password' => 'bar',
    ), 

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

    https://service.example.org/simplesaml/module.php/vootgroups/callback.php

This assumes that simpleSAMLphp is installed and reachable through 
`http://service.example.org/simplesaml`, modify the URL accordingly.

If you need to provide the `redirect_uri` as part of the authorize request as 
well you can also add the `redirect_uri` parameter to the `clientConfig` 
section of the configuration.

# SURFconext

For SURFconext you can use the following configuration:

## SURFconext API v 1 (VOOT 1.0):

    40 => array (
        'class' => 'vootgroups:AttributeAddVootGroups',
        'vootEndpoint' => 'https://api.surfconext.nl/v1/social/rest/groups/@me',
        'vootScope' => 'read',
        'targetAttribute' => 'isMemberOf',
        'userIdAttribute' => 'urn:mace:dir:attribute-def:eduPersonPrincipalName',
        'clientConfig' => array (
            'authorize_endpoint' => 'https://api.surfconext.nl/v1/oauth2/authorize',
            'redirect_uri' => 'https://service.example.org/simplesaml/module.php/vootgroups/callback.php',
            'client_id' => 'MY_SURFCONEXT_CLIENT_ID',
            'client_secret' => 'MY_SURFCONEXT_CLIENT_SECRET',
            'credentials_in_request_body' => true,
            'token_endpoint' => 'https://api.surfconext.nl/v1/oauth2/token',
        ),
        'storage' => array (
            'type' => 'SessionStorage',
        ),
    ),

## SURFconext API v 2 (VOOT 2.0):

    40 => array (
        'class' => 'vootgroups:AttributeAddVootGroups',
        'vootEndpoint' => 'https://voot.surfconext.nl/me/groups',
        'vootScope' => 'groups',
        'targetAttribute' => 'isMemberOf',
        'userIdAttribute' => 'urn:mace:dir:attribute-def:eduPersonPrincipalName',
        'clientConfig' => array (
            'authorize_endpoint' => 'https://authz.surfconext.nl/oauth/authorize',
            'redirect_uri' => 'https://service.example.org/simplesaml/module.php/vootgroups/callback.php',
            'client_id' => 'MY_SURFCONEXT_CLIENT_ID',
            'client_secret' => 'MY_SURFCONEXT_CLIENT_SECRET',
            'token_endpoint' => 'https://authz.surfconext.nl/oauth/token',
        ),
        'storage' => array (
            'type' => 'SessionStorage',
        ),
    ),

NOTE: you need to use an attribute for `userIdAttribute`. In the example
we use `eduPersonPricipalName`. Another candidate is `eduPersonTargetedID`. 
You may need to request permission to use this attribute when connecting your
service to SURFconext.

If you have a `client_id` with a colon (`:`) in it, make sure to also set
`'credentials_in_request_body' => true` in the `clientConfig` section.
 
Of course, you can replace `SessionStorage` with `PdoStorage` (see above) for
production setups.

# License

This module is free software, licensed under the Apache 2.0 license. See the file LICENSE for details.
