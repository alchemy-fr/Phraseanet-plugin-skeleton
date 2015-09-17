<?php
/*
 * This file is part of Phraseanet
 *
 * (c) 2005-2015 Alchemy
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Alchemy\SkeletonPlugin\ServiceProvider;

use Alchemy\Phrasea\Application as PhraseaApplication;
use Alchemy\Phrasea\Plugin\PluginProviderInterface;
use Alchemy\RestBundle\EventListener\ExceptionListener;
use Alchemy\RestBundle\EventListener\RequestMatchingExceptionListener;
use Alchemy\RestBundle\Rest\Response\ExceptionTransformer\JsonApiExceptionTransformer;
use Alchemy\RestProvider\RestProvider;
use Alchemy\SkeletonPlugin\ActionBar\ActionBarPlugin;
use Alchemy\SkeletonPlugin\Dbal\Type\UtcDateTimeType;
use Alchemy\SkeletonPlugin\EventStore\DbalEventStore;
use Alchemy\SkeletonPlugin\EventStore\DbalEventStoreSchema;
use Alchemy\SkeletonPlugin\EventStore\DbalRecordedEventMapper;
use Alchemy\SkeletonPlugin\EventStore\InMemoryEventStore;
use Alchemy\SkeletonPlugin\EventStore\RecordedEventFactory;
use Alchemy\SkeletonPlugin\Form\Type\RecordIdType;
use Alchemy\SkeletonPlugin\Form\Type\UserIdType;
use Alchemy\SkeletonPlugin\Form\Type\WebGalleryIdType;
use Alchemy\SkeletonPlugin\Locale\LocaleController;
use Alchemy\SkeletonPlugin\Projector\WebGalleryProjector;
use Alchemy\SkeletonPlugin\Record\CaptionTransformer;
use Alchemy\SkeletonPlugin\Record\RecordTransformer;
use Alchemy\SkeletonPlugin\Serialization\DateTimeHandler;
use Alchemy\SkeletonPlugin\Serialization\MappedNameResolver;
use Alchemy\SkeletonPlugin\Serialization\MetadataSerializer;
use Alchemy\SkeletonPlugin\Security\WebGalleryVoter;
use Alchemy\SkeletonPlugin\Transformer\WebGalleryItemTransformer;
use Alchemy\SkeletonPlugin\Transformer\WebGalleryTransformer;
use Alchemy\SkeletonPlugin\User\UserTransformer;
use Alchemy\SkeletonPlugin\Util\LazyLocator;
use Alchemy\SkeletonPlugin\Skeleton\AddRecordsToWebGalleryCommandHandler;
use Alchemy\SkeletonPlugin\Skeleton\ArchiveWebGalleryCommandHandler;
use Alchemy\SkeletonPlugin\Skeleton\CreateWebGalleryCommandHandler;
use Alchemy\SkeletonPlugin\Skeleton\DeleteWebGalleryCommandHandler;
use Alchemy\SkeletonPlugin\Skeleton\PublishWebGalleryCommandHandler;
use Alchemy\SkeletonPlugin\Skeleton\SkeletonController;
use Alchemy\SkeletonPlugin\Skeleton\SkeletonControllerProvider;
use Alchemy\SkeletonPlugin\Skeleton\WebGalleryItemRepository;
use Alchemy\SkeletonPlugin\Skeleton\WebGalleryRepository;
use Alchemy\SkeletonPlugin\WorkZone\WorkZonePlugin;
use Doctrine\DBAL\Types\Type;
use JMS\Serializer\SerializerInterface;
use Pimple;
use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use SimpleBus\JMSSerializerBridge\JMSSerializerObjectSerializer;
use SimpleBus\Message\Bus\Middleware\FinishesHandlingMessageBeforeHandlingNext;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;
use SimpleBus\Message\CallableResolver\CallableCollection;
use SimpleBus\Message\CallableResolver\CallableMap;
use SimpleBus\Message\CallableResolver\ServiceLocatorAwareCallableResolver;
use SimpleBus\Message\Handler\DelegatesToMessageHandlerMiddleware;
use SimpleBus\Message\Handler\Resolver\NameBasedMessageHandlerResolver;
use SimpleBus\Message\Name\ClassBasedNameResolver;
use SimpleBus\Message\Recorder\HandlesRecordedMessagesMiddleware;
use SimpleBus\Message\Recorder\PublicMessageRecorder;
use SimpleBus\Message\Subscriber\NotifiesMessageSubscribersMiddleware;
use SimpleBus\Message\Subscriber\Resolver\NameBasedMessageSubscriberResolver;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestMatcher;

class Service implements PluginProviderInterface, ControllerProviderInterface
{
    const Skeleton_TEXTDOMAIN = 'plugin-skeleton';

    public static function create(PhraseaApplication $app)
    {
        return new self();
    }

    public function register(Application $app)
    {
        $app['skeleton.test'] = function (PhraseaApplication $app) {
            return 'test' === $app->getEnvironment();
        };

        $app['skeleton.plugin'] = $this;
        $app['skeleton.name'] = 'skeleton';
        $app['skeleton.asset_namespace'] = 'plugin-skeleton';
        $app['plugin.locale.textdomains'][self::Skeleton_TEXTDOMAIN] = __DIR__ . '/../../locale';

        $app['skeleton.workzone'] = $app->share(
            function (PhraseaApplication $app) {
                return new WorkZonePlugin($app['skeleton.asset_namespace']);
            }
        );
        $app['skeleton.actionbar'] = $app->share(
            function (PhraseaApplication $app) {
                return new ActionBarPlugin(
                    $app['skeleton.name'],
                    $app['skeleton.asset_namespace'],
                    self::Skeleton_TEXTDOMAIN
                );
            }
        );

        $this->registerControllers($app);

        $app['plugin.workzone'] = $app->share($app->extend('plugin.workzone', function (Pimple $plugins) use ($app) {
            $plugin = $app['skeleton.workzone'];

            $plugins[spl_object_hash($plugin)] = $plugin;

            return $plugins;
        }));

        $app['plugin.actionbar'] = $app->share(
            $app->extend('plugin.actionbar', function (\Pimple $plugins) use ($app) {
                $plugin = $app['skeleton.actionbar'];
                $plugins[spl_object_hash($plugin)] = $plugin;
                return $plugins;
            })
        );

        
    }

    public function boot(Application $app)
    {
    }

    public function connect(Application $app)
    {
        /** @var ControllerCollection $controllers */
        $controllers = $app['controllers_factory'];

        $controllers->get('/locale', 'skeleton.controller.locale:indexAction');

        $controllers
            ->get('/view/{webGalleryId}', 'skeleton.controller.skeleton:viewAction')
            ->bind('skeleton_view');

        return $controllers;
    }

    /**
     * @param Application $app
     */
    private function registerControllers(Application $app)
    {
        $app['skeleton.controller.locale'] = $app->share(
            function (PhraseaApplication $app) {
                return new LocaleController(
                    $app,
                    $app['plugin.locale.textdomains'][self::Skeleton_TEXTDOMAIN],
                    self::Skeleton_TEXTDOMAIN
                );
            }
        );

        $providers = [
            ['/skeleton', 'skeleton.plugin'],
        ];
        foreach ($providers as $provider) {
            $app['plugin.controller_providers.root'][] = $provider;
        }
    }
}
