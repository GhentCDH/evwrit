<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Illuminate\Database\Eloquent\Relations\Relation;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function __construct(string $environment, bool $debug)
    {
        parent::__construct($environment, $debug);

        // add annotation Morph Map
        Relation::enforceMorphMap([
            'language' => 'App\Model\LanguageAnnotation',
            'lexis' => 'App\Model\LexisAnnotation',
            'morphology' => 'App\Model\MorphologyAnnotation',
            'handshift' => 'App\Model\HandshiftAnnotation',
            'morpho_syntactical' => 'App\Model\MorphoSyntacticalAnnotation',
            'orthography' => 'App\Model\OrthographyAnnotation',
            'typography' => 'App\Model\TypographyAnnotation',
            'ltsa' => 'App\Model\LayoutTextStructureAnnotation',
            'gtsa' => 'App\Model\GenericTextStructureAnnotation',
            'gts' => 'App\Model\GenericTextStructure',
            'lts' => 'App\Model\LayoutTextStructure',
        ]);
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->import('../config/{packages}/*.yaml');
        $container->import('../config/{packages}/'.$this->environment.'/*.yaml');

        if (is_file(\dirname(__DIR__).'/config/services.yaml')) {
            $container->import('../config/services.yaml');
            $container->import('../config/{services}_'.$this->environment.'.yaml');
        } elseif (is_file($path = \dirname(__DIR__).'/config/services.php')) {
            (require $path)($container->withPath($path), $this);
        }
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import('../config/{routes}/'.$this->environment.'/*.yaml');
        $routes->import('../config/{routes}/*.yaml');

        if (is_file(\dirname(__DIR__).'/config/routes.yaml')) {
            $routes->import('../config/routes.yaml');
        } elseif (is_file($path = \dirname(__DIR__).'/config/routes.php')) {
            (require $path)($routes->withPath($path), $this);
        }
    }
}
