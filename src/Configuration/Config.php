<?php

namespace Alchemy\SkeletonPlugin\Configuration;

use Symfony\Component\Yaml\Yaml;
use Alchemy\SkeletonPlugin\ServiceProvider;

class Config
{
    public static function getConfigFilename()
    {
        $config_dir = realpath(dirname(__FILE__) . "/../../../../config") . "/plugins/" . ServiceProvider::NAME;

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
        $content = Yaml::dump(['configuration' => $config]);

        file_put_contents(self::getConfigFilename(), $content);
    }
}
