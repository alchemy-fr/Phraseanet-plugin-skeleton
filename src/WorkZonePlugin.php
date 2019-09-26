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
    private $pluginName;

    public function __construct($pluginName)
    {
        $this->pluginName = $pluginName;
    }

    public function getWorkzoneTemplate()
    {
        return sprintf('%s/prod/workzone.html.twig', $this->pluginName);
    }
}
