<?php

namespace Silex\Bundle\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Silex\Api\EventListenerProviderInterface;
use Silex;
use Silex\Bundle\EventListener\ConverterListener;
use Silex\EventListener\MiddlewareListener;
use Silex\EventListener\StringToResponseListener;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use function assert;

class HttpKernelServiceProvider implements ServiceProviderInterface, EventListenerProviderInterface
{
    public function __construct(protected ContainerInterface $container)
    {
    }

    public function register(Container $pimple): void
    {
        assert($pimple instanceof Silex\Application);
        $pimple['resolver'] = fn ($app) => $this->container->get('controller_resolver');
        $pimple['argument_resolver'] = fn ($app) => $this->container->get('argument_resolver');
        $pimple['kernel'] = fn ($app) => $this->container->get('kernel');
        $pimple['request_stack'] = fn ($app) => $this->container->get('request_stack');
        $pimple['dispatcher'] = fn ($app) => $this->container->get('dispatcher');
        $pimple['callback_resolver'] = fn ($app) => new Silex\CallbackResolver($app);
    }

    public function subscribe(Container $app, EventDispatcherInterface $dispatcher)
    {
        assert($app instanceof Silex\Application);
        $dispatcher->addSubscriber(new StringToResponseListener());
        $dispatcher->addSubscriber(new MiddlewareListener($app));
        $dispatcher->addSubscriber(new ConverterListener($app['routes'], $app['callback_resolver']));
    }
}
