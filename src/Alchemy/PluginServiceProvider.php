<?php

/*
 * This file is part of Phraseanet graylog plugin
 *
 * (c) 2005-2013 Alchemy
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alchemy;

use Alchemy\Phrasea\Controller\RecordsRequest;
use Alchemy\Phrasea\Plugin\PluginProviderInterface;
use Alchemy\Phrasea\Application as PhraseaApplication;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class PluginServiceProvider implements PluginProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Application $app)
    {
        $app->post('/do-something/', array($this, 'doSomething'))->bind('do_something');
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {

    }

    /**
     * {@inheritdoc}
     */
    public static function create(PhraseaApplication $app)
    {
        return new static();
    }

    public   function doSomething(Application $app, Request $request)
    {
        $records = RecordsRequest::fromRequest($app, $request);

        return $app['twig']->render('prod/do_something.html.twig', [
            'records'   => $records
        ]);
    }
}