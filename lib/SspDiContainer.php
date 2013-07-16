<?php

class sspmod_vootgroups_SspDiContainer extends \Pimple
{
    public function __construct($config)
    {
        $this['clientConfig'] = function() use ($config) {
            return \fkooman\OAuth\Client\ClientConfig::fromArray($config['clientConfig']);
        };

        $this['vootEndpoint'] = function() use ($config) {
            return $config['vootEndpoint'];
        };

        $this['storage'] = function() use ($config) {
            if ("SessionStorage" === $config['storage']['type']) {
               return new \fkooman\OAuth\Client\SessionStorage();
            } elseif ("PdoStorage" === $config['storage']['type']) {
                $dsn = $config['storage']['dsn'];
                $username = $config['storage']['username'];
                $password = $config['storage']['password'];

                $db = new \PDO($dsn, $username, $password);
                $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

                if (0 === strpos($dsn, "sqlite:")) {
                    // only for SQlite
                    $db->exec("PRAGMA foreign_keys = ON");
                }

                $storage = new \fkooman\OAuth\Client\PdoStorage($db);

                return $storage;
            } else {
                throw new \Exception("unsupported storage backend");
            }
        };
    }
}
