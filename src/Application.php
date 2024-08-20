<?php

namespace Silex\Bundle;

use Pimple\ServiceProviderInterface;
use Silex\Application as BaseApplication;
use Silex\Provider as SilexProvider;

class Application extends BaseApplication
{
    /**
     * Registers a service provider.
     *
     * @param ServiceProviderInterface $provider A ServiceProviderInterface instance
     * @param array                    $values   An array of values that customizes the provider
     */
    public function register(ServiceProviderInterface $provider, array $values = []): static
    {
        // We override this function so that the default providers are not added.
        if ($provider instanceof SilexProvider\HttpKernelServiceProvider ||
            $provider instanceof SilexProvider\RoutingServiceProvider ||
            $provider instanceof SilexProvider\ExceptionHandlerServiceProvider
        ) {
            return $this;
        }
        return parent::register($provider, $values);
    }
}
