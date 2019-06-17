<?php

namespace Alchemy\SkeletonPlugin\BasketActionBar;

use Alchemy\Phrasea\Plugin\BasketActionBarPluginInterface;

class BasketActionBarPlugin implements BasketActionBarPluginInterface
{
    private $assetNamespace;
    private $pluginName;
    private $pluginLocale;

    public function __construct($pluginName, $assetNamespace, $pluginLocale)
    {
        $this->assetNamespace = $assetNamespace;
        $this->pluginName = $pluginName;
        $this->pluginLocale = $pluginLocale;
    }

    public function getBasketActionBar()
    {
        return [
            'add' => [
                'classes' => 'TOOL_skeleton_btn_2',
                'label'   => 'Skeleton_option_2',
                'icon'    => 'img/skeleton.png'
            ],
        ];
    }

    public function getPluginName()
    {
        return $this->pluginName;
    }

    public function getPluginLocale()
    {
        return $this->pluginLocale;
    }
}
