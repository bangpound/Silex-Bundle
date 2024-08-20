<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Silex\AppArgumentValueResolver;
use Silex\Application;
use Silex\Bundle\Provider\HttpKernelServiceProvider;
use Silex\Bundle\Provider\RoutingServiceProvider;
use Silex\Provider\Routing\RedirectableUrlMatcher;
use Symfony\Cmf\Component\Routing\DynamicRouter;
use Symfony\Component\HttpKernel\Config\FileLocator;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RouteCollection;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services
        ->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set('silex.provider.http_kernel', HttpKernelServiceProvider::class)
        ->args([
            service_locator([
                'resolver' => service('controller_resolver'),
                'argument_resolver' => service('argument_resolver'),
                'kernel' => service('http_kernel'),
                'request_stack' => service('request_stack'),
                'dispatcher' => service('event_dispatcher'),
            ]),
        ]);

    $services
        ->set('silex.provider.routing', RoutingServiceProvider::class)
        ->args([
            service_locator([
                'routes_factory' => service('silex.routes.factory'),
                'routes' => service('silex.routes'),
                'url_generator' => service('silex.url_generator'),
                'request_context' => service('router.request_context'),
            ]),
        ]);

    $services
        ->set('silex.app', param('silex.app.class'))
        ->args([
            '$values' => [
                'debug' => param('kernel.debug'),
                'logger' => service('logger'),
            ],
        ])
        ->call('register', [service('silex.provider.http_kernel')])
        ->call('register', [service('silex.provider.routing')])
        ->public();

    $services
        ->alias(Application::class, 'silex.app');

    $services
        ->set('silex.url_generator')
        ->class(UrlGenerator::class)
        ->args(['$routes' => service('silex.routes')]);

    $services
        ->set('silex.routes.factory', RouteCollection::class)
        ->autoconfigure(false)
        ->autowire(false)
        ->share(false)
        ->public();

    $services
        ->set(RedirectableUrlMatcher::class)
        ->args(['$routes' => service('silex.routes')]);

    $services
        ->set('silex.router')
        ->class(DynamicRouter::class)
        ->args([
            '$matcher' => service(RedirectableUrlMatcher::class),
            '$generator' => service('silex.url_generator'),
        ])
        ->tag('router');

    $services
        ->set('silex.routes', RouteCollection::class)
        ->parent('silex.routes.factory')
        ->public();

    $services
        ->set(AppArgumentValueResolver::class)
        ->tag('controller.argument_value_resolver');

    $services
        ->set('silex.file_locator', FileLocator::class)
        ->args([
            service('kernel'),
        ])
        ->public();
};
