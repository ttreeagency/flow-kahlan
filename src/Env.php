<?php
namespace Ttree\FlowKahlan;

use josegonzalez\Dotenv\Loader;
use Kahlan\Cli\Kahlan;
use Kahlan\Filter\Filter;
use Kahlan\Suite;
use TYPO3\Flow\Core\Bootstrap;

class Env
{
    /** @var string Application root folder */
    private static $rootPath;

    /** @var string Application web folder */
    private static $webPath;

    /** @var Env Singleton instance */
    private static $instance;

    /**
     * @param  Kahlan $kahlan
     * @param  string $base_path
     * @return void
     */
    public static function bootstrap(Kahlan $kahlan, $base_path = null)
    {
        self::$rootPath = $_SERVER['FLOW_ROOTPATH'] = $base_path ?: realpath(__DIR__ . '/../../../');
        self::$webPath = $_SERVER['FLOW_WEBPATH'] = $base_path ?: realpath(__DIR__ . '/../../../Web/');

        spl_autoload_register('TYPO3\Flow\Build\Env::loadClassForTesting');

        /*
        |--------------------------------------------------------------------------
        | Prepare environment variables
        |--------------------------------------------------------------------------
        */
        $env = self::$instance = new self;

        $commandLine = $kahlan->commandLine();

        $commandLine->option('env', ['array' => true]);

        $commandLine->option('no-flow', ['type' => 'boolean']);

        Filter::register('flow.env', function ($chain) use ($commandLine, $env) {
            $env->loadEnvFromFile('.env.kahlan');
            $env->loadEnvFromCli($commandLine);
            return $chain->next();
        });
        /*
        |--------------------------------------------------------------------------
        | Create Flow context for specs
        |--------------------------------------------------------------------------
        */
        Filter::register('flow.start', function ($chain) use ($commandLine, $env, $kahlan) {
            if ($commandLine->exists('no-flow') && !$commandLine->get('no-flow')
                || !$commandLine->exists('no-flow') && !getenv('NO_FLOW')
            ) {
                $kahlan->suite()->beforeAll($env->refreshApplication());
                $kahlan->suite()->beforeEach($env->refreshApplication());
            }
            return $chain->next();
        });

        /*
        |--------------------------------------------------------------------------
        | Apply customizations
        |--------------------------------------------------------------------------
        */
        Filter::apply($kahlan, 'interceptor', 'flow.env');
        Filter::apply($kahlan, 'interceptor', 'flow.start');
    }

    /**
     * A simple class loader that deals with the Framework classes and is intended
     * for use with unit tests executed by PHPUnit.
     *
     * @param string $className
     * @return void
     */
    public static function loadClassForTesting($className)
    {
        $classNameParts = explode('\\', $className);
        if (!is_array($classNameParts)) {
            return;
        }

        foreach (new \DirectoryIterator(__DIR__ . '/../../../Packages/') as $fileInfo) {
            if (!$fileInfo->isDir() || $fileInfo->isDot() || $fileInfo->getFilename() === 'Libraries') {
                continue;
            }

            $classFilePathAndName = $fileInfo->getPathname() . '/';
            foreach ($classNameParts as $index => $classNamePart) {
                $classFilePathAndName .= $classNamePart;
                if (file_exists($classFilePathAndName . '/Classes')) {
                    $packageKeyParts = array_slice($classNameParts, 0, $index + 1);
                    $classesOrTests = ($classNameParts[$index + 1] === 'Tests' && isset($classNameParts[$index + 2]) && $classNameParts[$index + 2] === 'Unit') ? '/' : '/Classes/' . implode('/', $packageKeyParts) . '/';
                    $classesFilePathAndName = $classFilePathAndName . $classesOrTests . implode('/', array_slice($classNameParts, $index + 1)) . '.php';
                    if (is_file($classesFilePathAndName)) {
                        require($classesFilePathAndName);
                        break;
                    }
                }
                $classFilePathAndName .= '.';
            }
        }
    }

    /**
     * Provide fresh application instance for each single spec.
     *
     * @return \Closure
     */
    public function refreshApplication()
    {
        return function () {
            $context = Suite::current();
            $context->bootstrap = $this->bootstrapFlow();
        };
    }

    /**
     * Bootstrap Flow application.
     *
     * @return Bootstrap
     */
    protected function bootstrapFlow()
    {
        return new Bootstrap('Testing');
    }

    /**
     * Load environment variables from kahlan-specific env file if provided.
     *
     * @param  string $filename
     * @return void
     */
    public function loadEnvFromFile($filename)
    {
        putenv('BASE_URL=http://localhost');
        if (is_readable($filename) && is_file($filename)) {
            (new Loader(self::$rootPath, $filename))->load();
        }
    }

    /**
     * Load environment variables provided in CLI at runtime.
     *
     * @param  \Kahlan\Cli\CommandLine $commandLine
     * @return void
     */
    public function loadEnvFromCli($commandLine)
    {
        $env = [
            'FLOW_CONTEXT' => 'Testing',
            'FLOW_REWRITEURLS' => 1
        ];
        foreach ($commandLine->get('env') as $key => $val) {
            foreach (explode(',', $val) as $arg) {
                list($k, $v) = preg_split('/:|=/', $arg);
                $env[$k] = $v;
            }
        }
        foreach ($env as $key => $val) {
            putenv($key . '=' . $val);
        }
    }
}
