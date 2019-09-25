<?php

namespace Alchemy\SkeletonPlugin;

use Alchemy\SkeletonPlugin\Configuration\Config;
use Alchemy\SkeletonPlugin\Form\ConfigurationType;
use Alchemy\SkeletonPlugin\Security\PluginConfigurationVoter;
use Alchemy\SkeletonPlugin\Security\PluginVoter;
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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ServiceProvider implements PluginProviderInterface
{
    const NAME = 'phraseanet-plugin-skeleton';
    const VERSION = '2.0.0';

    /**
     * {@inheritdoc}
     *
     */
    public function register(Application $app)
    {
        $app[self::NAME . '.config'] = $app->share(
            function (/** @noinspection PhpUnusedParameterInspection */ Application $app) {

                return Config::getConfiguration();
            }
        );

        $app['plugin.locale.textdomains'][self::NAME] = __DIR__ . '/../../locale';

        // register translator resource
        $app['translator'] = $app->share(
            $app->extend('translator',
                function (Translator $translator, /** @noinspection PhpUnusedParameterInspection */
                          Application $app) {

                    $translator->addResource('po', __DIR__ . '/../locale/en_GB.po', 'en', self::NAME);
                    $translator->addResource('po', __DIR__ . '/../locale/fr_FR.po', 'fr', self::NAME);

                    return $translator;
                }
            )
        );


        $app[self::NAME . '.actionbar'] = $app->share(
            function (/** @noinspection PhpUnusedParameterInspection */ PhraseaApplication $app) {
                return new ActionBarPlugin(
                    self::NAME
                );
            }
        );

        $app[self::NAME . '.workzone'] = $app->share(
            function (/** @noinspection PhpUnusedParameterInspection */ PhraseaApplication $app) {
                return new WorkZonePlugin(
                    self::NAME
                );
            }
        );

        $app[self::NAME . '.workzone.basket.actionbar'] = $app->share(
            function (/** @noinspection PhpUnusedParameterInspection */ PhraseaApplication $app) {
                return new BasketActionBarPlugin(
                    self::NAME
                );
            }
        );


        // register voters
        $this->registerVoters($app);
        // register admin tab
        $this->registerConfigurationTabs($app);


        $app['plugin.actionbar'] = $app->share(
            $app->extend('plugin.actionbar',
                function (\Pimple $plugins) use ($app) {
                    $plugin = $app[self::NAME . '.actionbar'];
                    $plugins[spl_object_hash($plugin)] = $plugin;

                    return $plugins;
                }
            )
        );

        $app['plugin.workzone'] = $app->share($app->extend('plugin.workzone', function (Pimple $plugins) use ($app) {
            $plugin = $app[self::NAME . '.workzone'];

            $plugins[spl_object_hash($plugin)] = $plugin;

            return $plugins;
        }));


        $app['plugin.workzone.basket.actionbar'] = $app->share(
            $app->extend('plugin.workzone.basket.actionbar',
                function (\Pimple $plugins) use ($app) {
                    $plugin = $app[self::NAME . '.workzone.basket.actionbar'];
                    $plugins[spl_object_hash($plugin)] = $plugin;

                    return $plugins;
                }
            )
        );


        // define the routes
        /** @var Firewall $firewall */
        $firewall = $this->getFirewall($app);

        // route for option 1 of actionbar "push" dropdown
        $app->post('/skeleton_1/', [$this, 'skeleton_1'])->bind('skeleton_1');

        // route for option 2 of actionbar "push" dropdown
        $app->post('/skeleton_2/', [$this, 'skeleton_2'])->bind('skeleton_2');

        // admin conf
        $app->match('/' . self::NAME . '/configuration', [$this, 'adminConfiguration'])
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
        $plugins[self::NAME] = $plugins->share(function () use ($app) {
            return new BasePluginMetadata(
                self::NAME,
                self::VERSION,
                '',
                self::NAME, // text-domain
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
            'text_domain'      => self::NAME,   // text-domain
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
    public function adminConfiguration(PhraseaApplication $app, Request $request)
    {
        $config = Config::getConfiguration();

        $form = $app->form(new ConfigurationType(), $config['skeleton']);

        $form->handleRequest($request);

        if ($form->isValid()) {
            Config::setConfiguration($form->getData());

            return $app->redirectPath('admin_plugins_list');
        }

        return $app['twig']->render(self::NAME .  '/admin/configuration.html.twig', [
            'form_action' => '/' . self::NAME . '/configuration',
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

                $voters[] = new PluginVoter($app['repo.users'], $app[self::NAME . '.config']);
                $voters[] = new PluginConfigurationVoter($app['repo.users']);

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
            /** @var UrlGeneratorInterface $urlGeneraton */
            $urlGenerator = $app['url_generator'];
            return new ConfigurationTab(
                $urlGenerator->generate('skeleton_admin_configuration')
            );
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
