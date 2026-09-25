<?php

namespace App\DependencyInjection;

use App\Api\Schema\GenericLookupSchema;
use App\Model\IdNameModel;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Registers a {@see GenericLookupSchema} service for every IdName lookup model, so each one
 * is exposed as an API lookup service without a hand-written schema class.
 *
 * A model is skipped when it is denylisted, or when a concrete schema already exists for it
 * (convention: App\Api\Schema\Model\Lookup\<Model>Schema) — that concrete class wins.
 */
class RegisterLookupSchemasPass implements CompilerPassInterface
{
    private const LOOKUP_NAMESPACE = 'App\\Model\\Lookup\\';
    private const SCHEMA_NAMESPACE = 'App\\Api\\Schema\\Model\\Lookup\\';

    /**
     * Models to NOT expose as lookup services. Add fully-qualified class names here.
     *
     * @var list<class-string>
     */
    private const DENYLIST = [];

    public function process(ContainerBuilder $container): void
    {
        $dir = $container->getParameter('kernel.project_dir').'/src/Model/Lookup';
        $files = glob($dir.'/*.php') ?: [];

        foreach ($files as $file) {
            $class = self::LOOKUP_NAMESPACE.basename($file, '.php');

            if (!class_exists($class) || !is_subclass_of($class, IdNameModel::class)) {
                continue;
            }
            if (in_array($class, self::DENYLIST, true)) {
                continue;
            }
            // A hand-written concrete schema takes precedence over the generic one.
            if (class_exists(self::SCHEMA_NAMESPACE.(new \ReflectionClass($class))->getShortName().'Schema')) {
                continue;
            }

            $definition = new Definition(GenericLookupSchema::class, [$class]);
            $definition->addTag('app.api.schema');
            $container->setDefinition('app.api.lookup_schema.'.$class, $definition);
        }
    }
}
