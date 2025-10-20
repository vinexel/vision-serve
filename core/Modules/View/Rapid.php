<?php

/**
 * Vinexel Framework.
 *
 * @package Vision
 * @author Elwira Perdana
 * @copyright (c) PT Iconic Wira Niaga
 * @license MIT License
 */

namespace Vinexel\Modules\View;

use Twig\Environment;
use Twig\TwigFunction;
use Twig\Loader\FilesystemLoader;
use Vinexel\Modules\Globals\Language\Lang;
use Vision\Modules\Config;
use Deeper\Libraries\Flasher;
use Deeper\Globals\Config\Src\SysCon as SC;

abstract class Rapid
{
    protected $twig;

    public function __construct($basePath, $enableCache = false)
    {
        $cachePath = $enableCache ?
            VISION_DIR . DIRECTORY_SEPARATOR . 'system/storage/cache' . DIRECTORY_SEPARATOR . PROJECT_NAME . DIRECTORY_SEPARATOR
            : false;

        $loader = new FilesystemLoader($basePath);
        $this->twig = new Environment($loader, [
            'cache' => $cachePath,
            'debug' => false,
        ]);

        $this->registerFunctions();
    }

    /**
     * Register helper functions in Twig
     */
    public function registerFunctions()
    {
        $helperDir = VISION_DIR . '/app/' . PROJECT_NAME . '/Libraries/Helper';
        if (!is_dir($helperDir)) return;

        $before = get_defined_functions()['user'];

        // Load all helper file
        foreach (glob($helperDir . '/*.php') as $file) {
            require_once $file;
        }

        $after = get_defined_functions()['user'];
        $newFunctions = array_diff($after, $before);

        foreach ($newFunctions as $fn) {
            $this->twig->addFunction(new TwigFunction($fn, $fn));
        }

        foreach (glob($helperDir . '/*.php') as $file) {
            $content = file_get_contents($file);
            if (
                preg_match('/namespace\s+(.+?);/', $content, $nsMatch) &&
                preg_match('/class\s+([^\s]+)/', $content, $classMatch)
            ) {

                $fullClass = $nsMatch[1] . '\\' . $classMatch[1];
                if (class_exists($fullClass)) {
                    $methods = get_class_methods($fullClass);
                    foreach ($methods as $method) {
                        $callable = [$fullClass, $method];
                        if (is_callable($callable)) {
                            $this->twig->addFunction(new TwigFunction($method, $callable));
                        }
                    }
                }
            }
        }
    }
}
