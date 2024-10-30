<?php

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\Request;

require dirname(__DIR__).'/vendor/autoload.php';

(new Dotenv())->bootEnv(dirname(__DIR__).'/.env');

if ($_SERVER['APP_DEBUG']) {
    umask(0000);

    Debug::enable();
}

// add annotation Morph Map
use Illuminate\Database\Eloquent\Relations\Relation;

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

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
