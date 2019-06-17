<?php

namespace Alchemy\SkeletonPlugin\ActionBar;

use Alchemy\Phrasea\Plugin\ActionBarPluginInterface;
use Twig_Environment;


class ActionBarPlugin implements ActionBarPluginInterface
{
    private $assetNamespace;
    private $pluginName;
    private $pluginLocale;
    /** @var Twig_Environment */
    private $twig;

    public function __construct($pluginName, $assetNamespace, $pluginLocale, $twig)
    {
        $this->assetNamespace = $assetNamespace;
        $this->pluginName = $pluginName;
        $this->pluginLocale = $pluginLocale;
        $this->twig = $twig;
    }

    public function getActionBar()
    {
        return [
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
    }

    public function getPluginName()
    {
        return $this->pluginName;
    }

    public function getPluginLocale()
    {
        return $this->pluginLocale;
    }

    public function getJS()
    {
        return $this->twig->render('phraseanet-plugin-skeleton/prod/toolbar.html.twig', []);
    }
}
