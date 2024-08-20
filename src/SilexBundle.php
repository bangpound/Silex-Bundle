<?php

namespace Silex\Bundle;

use Silex\Bundle\Loader\PhpFileLoader;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class SilexBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->scalarNode('app_class')
                    ->defaultValue(Application::class)
                ->end() // app_class
                ->arrayNode('files')
                    ->scalarPrototype()->end()
                ->end() // files
            ->end()
        ->end();
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.php');

        $parameters = $container->parameters();

        $parameters->set('silex.app.class', $config['app_class']);
        $parameters->set('silex.files', $config['files']);
    }

    public function boot(): void
    {
        $app = $this->container->get('silex.app');
        $files = $this->container->getParameter('silex.files');
        $locator = $this->container->get('silex.file_locator');
        $loader = new PhpFileLoader($app, $locator);
        foreach ($files as $file) {
            $loader->load($file);
        }
        $app->flush();
        $app->boot();
    }
}
