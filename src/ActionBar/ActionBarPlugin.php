<?php

/*
 * This file is part of Phraseanet
 *
 * (c) 2005-2015 Alchemy
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Alchemy\SkeletonPlugin\ActionBar;

use Alchemy\Phrasea\Plugin\ActionBarPluginInterface;

class ActionBarPlugin implements ActionBarPluginInterface
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

    public function getActionBar()
    {
        return [
            'tools' => [],
            'push'  => [
                'add' => [
                    'classes' => 'TOOL_addWebGallery_btn',
                    'label'   => 'plugin_wg.actionbar.push.addgallery',
                    'icon'    => ''
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
}
