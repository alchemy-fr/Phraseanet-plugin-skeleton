<?php

namespace Alchemy\SkeletonPlugin\Configuration;

use Alchemy\Phrasea\Plugin\ConfigurationTabInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class ConfigurationTab
 * @package Alchemy\SkeletonkPlugin\Configuration
 */
class ConfigurationTab implements ConfigurationTabInterface
{
    private $url;

    public function __construct($url)
    {
        $this->url = $url;
    }

    public function getTitle()
    {
        return 'configuration_tab';
    }

    public function getUrl()
    {
        return $this->url;
    }
}
