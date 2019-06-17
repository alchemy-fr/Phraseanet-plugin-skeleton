<?php

namespace Alchemy\SkeletonPlugin\Configuration;

use Symfony\Component\Yaml\Yaml;

class Config
{
    public static function getConfigFilename()
    {
        $manifest_name = json_decode(file_get_contents(realpath(dirname(__FILE__)) . "/../../manifest.json"))->name;
        $config_dir = realpath(dirname(__FILE__) . "/../../../../config") . "/plugins/" . $manifest_name;

        if (!is_dir($config_dir)) {
            mkdir($config_dir);
        }

        $config_file = $config_dir . '/configuration.yml';

        return ($config_file);
    }

    public static function getConfiguration()
    {
        $config = null;
        // locate the config for this plugin
        $config_file = self::getConfigFilename();

        if (file_exists($config_file)) {
            try {
                $config = Yaml::parse(file_get_contents($config_file));
            }
            catch (\Exception $e) {
                return null;
            }
        }

        return $config;
    }

    public static function setConfiguration(array $config)
    {
        $content = Yaml::dump(['skeleton' => $config]);

        file_put_contents(self::getConfigFilename(), $content);
    }
}
