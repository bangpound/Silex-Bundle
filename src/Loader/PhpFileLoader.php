<?php

namespace Silex\Bundle\Loader;

use ReflectionFunction;
use Silex\Application;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Config\Loader\FileLoader;

class PhpFileLoader extends FileLoader
{
    private Application $app;
    public function __construct(Application $app, FileLocatorInterface $locator, ?string $env = null)
    {
        parent::__construct($locator, $env);
        $this->app = $app;
    }

    public function load(mixed $resource, ?string $type = null): mixed
    {
        // the container and loader variables are exposed to the included file below
        $app = $this->app;
        $loader = $this;

        $path = $this->locator->locate($resource);
        $this->setCurrentDir(\dirname($path));

        // the closure forbids access to the private scope in the included file
        $load = \Closure::bind(function ($path, $env) use ($app, $loader, $resource, $type) {
            return include $path;
        }, $this, ProtectedPhpFileLoader::class);

        $callback = $load($path, $this->env);

        if (\is_object($callback) && \is_callable($callback)) {
            $this->executeCallback($callback, $this->app, $path);
        }

        return $app;
    }

    public function supports(mixed $resource, ?string $type = null): bool
    {
        if (!\is_string($resource)) {
            return false;
        }

        if (null === $type && 'php' === pathinfo($resource, \PATHINFO_EXTENSION)) {
            return true;
        }

        return 'php' === $type;
    }

    /**
     * Resolve the parameters to the $callback and execute it.
     * @throws \ReflectionException
     */
    private function executeCallback(callable $callback, Application $app, string $path): void
    {
        $callback = $callback(...);
        $arguments = [];
        $r = new ReflectionFunction($callback);

        foreach ($r->getParameters() as $parameter) {
            $reflectionType = $parameter->getType();
            if (!$reflectionType instanceof \ReflectionNamedType) {
                throw new \InvalidArgumentException(sprintf('Could not resolve argument "$%s" for "%s". You must typehint it (for example with "%s").', $parameter->getName(), $path, Application::class));
            }
            $type = $reflectionType->getName();

            switch ($type) {
                case Application::class:
                    $arguments[] = $app;
                    break;
                case \Symfony\Component\DependencyInjection\Loader\FileLoader::class:
                case self::class:
                    $arguments[] = $this;
                    break;
                case 'string':
                    if (null !== $this->env && 'env' === $parameter->getName()) {
                        $arguments[] = $this->env;
                        break;
                    }
                    break;
                default:
                    break;
            }
        }

        $callback(...$arguments);
    }
}

final class ProtectedPhpFileLoader extends PhpFileLoader
{
}
