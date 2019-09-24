<?php

namespace Alchemy\SkeletonPlugin;

use Alchemy\Phrasea\Plugin\ActionBarPluginInterface;
use Twig_Environment;


class ActionBarPlugin implements ActionBarPluginInterface
{
    // private $assetsNamespace;
    private $pluginName;
    // private $pluginLocale;

    public function __construct($pluginName) //, $assetsNamespace, $pluginLocale)
    {
        // $this->assetsNamespace = $assetsNamespace;
        $this->pluginName = $pluginName;
        // $this->pluginLocale = $pluginLocale;
    }

    public function getActionBar()
    {
        $menu = [
            'push' => [
                '1' => [
                    'classes' => 'TOOL_skeleton_btn_1',
                    'label'   => 'Skeleton_option_1',
                    'icon'    => 'img/skeleton.png'
                ],
                '2' => [
                    'classes' => 'TOOL_skeleton_btn_2',
                    'label'   => 'Skeleton_option_2',
                    'icon'    => 'img/skeleton.png'
                ],
            ],
        ];

        return $menu;
    }

    public function getAssetsNamespace()
    {
        // return $this->assetsNamespace;
        return $this->pluginName;
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

    public function getActionbarTemplate()
    {
        return sprintf('%s/prod/actionbar.html.twig', $this->pluginName);
    }
}
