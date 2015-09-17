<?php
/*
 * This file is part of Phraseanet
 *
 * (c) 2005-2015 Alchemy
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Alchemy\SkeletonPlugin\Locale;

use Alchemy\Phrasea\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\Loader\PoFileLoader;

class LocaleController
{
    public $app;
    private $localeDir;
    private $textDomain;

    public function __construct(Application $app, $localeDir, $textDomain)
    {
        $this->app = $app;
        $this->localeDir = $localeDir;
        $this->textDomain = $textDomain;
    }

    public function indexAction(Request $request)
    {
        $loader = new PoFileLoader();
        $path = sprintf('%s/%s/LC_MESSAGES/%s.po', $this->localeDir, $request->getLocale(), $this->textDomain);
        $catalogue = $loader->load($path, $request->getLocale());
        $messages = $catalogue->all();

        return $this->app->json($messages['messages']);
    }
}
