<?php
/*
 * This file is part of Phraseanet
 *
 * (c) 2005-2015 Alchemy
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Alchemy\SkeletonPlugin;

class WorkZonePlugin
{
    // private $assetsNamespace;
    private $pluginName;
    // private $pluginLocale;

    public function __construct($pluginName) // , $assetsNamespace, $pluginLocale)
    {
        // $this->assetsNamespace = $assetsNamespace;
        $this->pluginName = $pluginName;
        // $this->pluginLocale = $pluginLocale;
    }

    public function getWorkzoneTemplate()
    {
        return sprintf('%s/prod/workzone.html.twig', $this->pluginName);
    }
}
