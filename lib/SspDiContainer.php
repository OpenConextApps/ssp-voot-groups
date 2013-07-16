<?php

class sspmod_vootgroups_SspDiContainer extends \Pimple
{
    public function __construct($config)
    {
        $this['clientConfig'] = function() use ($config) {
            if (!isset($config['clientConfig'])) {
                throw new \Exception("missing configuration option 'clientConfig'");
            }

            return \fkooman\OAuth\Client\ClientConfig::fromArray($config['clientConfig']);
        };

        $this['vootEndpoint'] = function() use ($config) {
            if (!isset($config['vootEndpoint'])) {
                throw new \Exception("missing configuration option 'vootEndpoint'");
            }

            return $config['vootEndpoint'];
        };

        $this['targetAttribute'] = function() use ($config) {
            return isset($config['targetAttribute']) ? $config['targetAttribute'] : "isMemberOf";
        };

        $this['storage'] = function() use ($config) {
            if (!isset($config['storage']['type'])) {
                throw new \Exception("missing configuration option 'storage' => 'type'");
            }
            if ("SessionStorage" === $config['storage']['type']) {
               return new \fkooman\OAuth\Client\SessionStorage();
            } elseif ("PdoStorage" === $config['storage']['type']) {
                if (!isset($config['storage']['dsn'])) {
                    throw new \Exception("missing configuration option 'storage' => 'dsn'");
                }
                $dsn = $config['storage']['dsn'];
                $username = isset($config['storage']['username']) ? $config['storage']['username'] : null;
                $password = isset($config['storage']['password']) ? $config['storage']['password'] : null;

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
