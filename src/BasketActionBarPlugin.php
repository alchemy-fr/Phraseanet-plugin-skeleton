<?php

namespace Alchemy\SkeletonPlugin;

use Alchemy\Phrasea\Plugin\BasketActionBarPluginInterface;

class BasketActionBarPlugin implements BasketActionBarPluginInterface
{
    private $pluginName;

    public function __construct($pluginName)
    {
        $this->pluginName = $pluginName;
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
        // return $this->pluginLocale;
        return $this->pluginName;
    }
}
