<?php

namespace Silex\Bundle\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Silex;
use Symfony\Component\HttpFoundation\UrlHelper;

use function assert;

class RoutingServiceProvider implements ServiceProviderInterface
{
    public function __construct(protected ContainerInterface $container)
    {
    }

    public function register(Container $pimple): void
    {
        assert($pimple instanceof Silex\Application);
        $pimple['route_class'] = Silex\Route::class;
        $pimple['route_factory'] = $pimple->factory(fn ($app) => new $pimple['route_class']());
        $pimple['routes_factory'] = $pimple->factory(fn ($app) => $this->container->get('routes_factory'));
        $pimple['routes'] = fn ($app) => $this->container->get('routes');
        $pimple['url_generator'] = fn ($app) => $this->container->get('url_generator');
        $pimple['request_context'] = fn ($app) => $this->container->get('request_context');
        $pimple['url_helper'] = fn ($app) => new UrlHelper($pimple['request_stack'], $pimple['request_context']);
        $pimple['controllers'] = fn ($app) => $pimple['controllers_factory'];

        $controllers_factory = function ($app) use (&$controllers_factory) {
            return new Silex\ControllerCollection($app['route_factory'], $app['routes_factory'], $controllers_factory);
        };
        $pimple['controllers_factory'] = $pimple->factory($controllers_factory);
    }
}
