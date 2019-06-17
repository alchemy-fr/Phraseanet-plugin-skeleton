<?php

namespace Alchemy\SkeletonPlugin\ServiceProvider;

use Alchemy\SkeletonPlugin\BasketActionBar\BasketActionBarPlugin;
use Alchemy\SkeletonPlugin\ActionBar\ActionBarPlugin;
use Alchemy\SkeletonPlugin\Configuration\Config;
use Alchemy\SkeletonPlugin\Form\SkeletonConfigurationType;
use Alchemy\SkeletonPlugin\Security\SkeletonPluginConfigurationVoter;
use Alchemy\SkeletonPlugin\Security\SkeletonPluginVoter;
use Alchemy\SkeletonPlugin\Configuration\ConfigurationTab;
use Alchemy\Phrasea\Controller\RecordsRequest;
use Alchemy\Phrasea\Plugin\BasePluginMetadata;
use Alchemy\Phrasea\Plugin\PluginProviderInterface;
use Alchemy\Phrasea\Application as PhraseaApplication;
use Alchemy\Phrasea\Security\Firewall;
use Silex\Application;
use Silex\Translator;
use Symfony\Component\HttpFoundation\Request;
use Pimple;

class SkeletonPluginServiceProvider implements PluginProviderInterface
{
    const SKELETON_TEXTDOMAIN = 'plugin-skeleton';

    /**
     * {@inheritdoc}
     *
     */
    public function register(Application $app)
    {
        $app['plugin.skeleton.config'] = $app->share(
            function (/** @noinspection PhpUnusedParameterInspection */
                Application $app) {

                return Config::getConfiguration();
            }
        );

        $app['skeleton.name'] = 'phraseanet-plugin-skeleton';
        $app['skeleton.version'] = '2.0.0';
        $app['skeleton.asset_namespace'] = 'plugin-skeleton';

        $app['skeleton.actionbar'] = $app->share(
            function (PhraseaApplication $app) {
                return new ActionBarPlugin(
                    $app['skeleton.name'],
                    $app['skeleton.asset_namespace'],
                    self::SKELETON_TEXTDOMAIN,
                    $app['twig']
                );
            }
        );

        $app['plugin.actionbar'] = $app->share(
            $app->extend('plugin.actionbar',
                function (\Pimple $plugins) use ($app) {
                    $plugin = $app['skeleton.actionbar'];
                    $plugins[spl_object_hash($plugin)] = $plugin;

                    return $plugins;
                }
            )
        );

        $app['skeleton.workzone.basket.actionbar'] = $app->share(
            function (PhraseaApplication $app) {
                return new BasketActionBarPlugin(
                    $app['skeleton.name'],
                    $app['skeleton.asset_namespace'],
                    self::SKELETON_TEXTDOMAIN
                );
            }
        );

        $app['plugin.workzone.basket.actionbar'] = $app->share(
            $app->extend('plugin.workzone.basket.actionbar',
                function (\Pimple $plugins) use ($app) {
                    $plugin = $app['skeleton.workzone.basket.actionbar'];
                    $plugins[spl_object_hash($plugin)] = $plugin;

                    return $plugins;
                }
            )
        );

        // register translator resource
        $app['translator'] = $app->share(
            $app->extend('translator',
                function (Translator $translator, /** @noinspection PhpUnusedParameterInspection */
                          Application $app) {

                    $translator->addResource('po', __DIR__ . '/../../locale/en_GB/plugin-skeleton.po', 'en', 'plugin-skeleton');
                    $translator->addResource('po', __DIR__ . '/../../locale/fr_FR/plugin-skeleton.po', 'fr', 'plugin-skeleton');

                    return $translator;
                }
            )
        );

        // register voters
        $this->registerVoters($app);

        // register admin tab
        $this->registerConfigurationTabs($app);


        // define the routes
        /** @var Firewall $firewall */
        $firewall = $this->getFirewall($app);

        // route for option 1 of actionbar "push" dropdown
        $app->post('/skeleton_1/', [$this, 'skeleton_1'])->bind('skeleton_1');

        // route for option 2 of actionbar "push" dropdown
        $app->post('/skeleton_2/', [$this, 'skeleton_2'])->bind('skeleton_2');

        // admin conf
        $app->match('/skeleton/configuration', [$this, 'skeletonAdminConfiguration'])
            ->method('GET|POST')
            ->before(function () use ($firewall) {
                $firewall->requireAccessToModule('admin');
            })
            ->bind('skeleton_admin_configuration');
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {
        /** @var Pimple $plugins */
        $plugins = $app['plugins'];
        $plugins[$app['skeleton.name']] = $plugins->share(function () use ($app) {
            return new BasePluginMetadata(
                $app['skeleton.name'],
                $app['skeleton.version'],
                '',
                self::SKELETON_TEXTDOMAIN,
                $app['skeleton.configuration_tabs']
            );
        });
    }

    /**
     * {@inheritdoc}
     */
    public static function create(PhraseaApplication $app)
    {
        return new static();
    }

    /**
     * @param PhraseaApplication $app
     * @param Request $request
     * @return mixed
     *
     * route handler for option 1 of actionbar "push" dropdown
     */
    public function skeleton_1(/** @noinspection PhpUnusedParameterInspection */ PhraseaApplication $app,
        /** @noinspection PhpUnusedParameterInspection */Request $request)
    {
        return ("hello");
    }


    /**
     * @param PhraseaApplication $app
     * @param Request $request
     * @return mixed
     *
     * route handler for option 2 of actionbar "push" dropdown
     */
    public function skeleton_2(PhraseaApplication $app, Request $request)
    {
        $records = RecordsRequest::fromRequest($app, $request);

        $flattenedRecords = [];     // flattened records
        $basketName = null;         // in case we act on basket

        // ----- fct to add record(s) to params, flattening stories
        $addRecord = function(\record_adapter $r) use(&$flattenedRecords, &$addRecord) {
            if($r->isStory()) {
                foreach($r->getChildren() as $child) {
                    $addRecord($child);
                }
            }
            else {
                $flattenedRecords[$r->getId()] = $r;  // keys ensure unicity
            }
        };
        // -----

        // if we act on basket, extract content
        if ($records->basket()) {
            $basketName = $records->basket()->getName();
            foreach($records->basket()->getElements() as $basketElement) {
                $addRecord($basketElement->getRecord($app));
            }
        }
        else {
            foreach ($records as $record) {
                $addRecord($record);
            }
        }

        return $app['twig']->render('prod/skeleton_dialog_2.html.twig', [
            'dialog_level'     => $request->get('dialog_level'),
            'records'          => $records,
            'flattenedRecords' => $flattenedRecords,
            'basketName'       => $basketName
        ]);
    }

    /**
     * @param PhraseaApplication $app
     * @param Request $request
     * @return mixed
     */
    public function skeletonAdminConfiguration(PhraseaApplication $app, Request $request)
    {
        $config = Config::getConfiguration();

        $form = $app->form(new SkeletonConfigurationType(), $config['skeleton']);

        $form->handleRequest($request);

        if ($form->isValid()) {
            Config::setConfiguration($form->getData());

            return $app->redirectPath('admin_plugins_list');
        }

        return $app['twig']->render('phraseanet-plugin-skeleton/admin/skeleton_configuration.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @param Application $app
     */
    private function registerVoters(Application $app)
    {
        $app['phraseanet.voters'] = $app->share(
            $app->extend('phraseanet.voters', function (array $voters, PhraseaApplication $app) {

                $voters[] = new SkeletonPluginVoter($app);
                $voters[] = new SkeletonPluginConfigurationVoter($app['repo.users']);

                return $voters;
            })
        );
    }

    /**
     * @param Application $app
     */
    private function registerConfigurationTabs(Application $app)
    {
        $app['skeleton.configuration_tabs'] = [
            'configuration' => 'skeleton.configuration_tabs.configuration',
        ];

        $app['skeleton.configuration_tabs.configuration'] = $app->share(function (PhraseaApplication $app) {
            return new ConfigurationTab($app['url_generator']);
        });
    }

    /**
     * @param Application $app
     * @return mixed
     */
    private function getFirewall(Application $app)
    {
        return $app['firewall'];
    }
}
