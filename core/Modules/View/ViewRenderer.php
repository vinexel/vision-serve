<?php

namespace Vinexel\Modules\View;

use \Twig\Environment;
use \Twig\TwigFunction;
use \Twig\Loader\FilesystemLoader;
use \Vinexel\Modules\Globals\Language\Lang;
use \Vinexel\Modules\Debug\Debugger;
use \Vision\Modules\Requirements;
use \Vision\Modules\Config;
use \Deeper\Libraries\Flasher;
use \Deeper\Globals\Config\Src\SysCon as SC;
use Exception;

/**
 * ViewRenderer class
 *
 * Handles view rendering in the Vinexel Framework.
 * Supports both Twig templates (.rapid.php) and native PHP views.
 * It also provides helper functions for localization, flash messages,
 * and reusable partials.
 */
class ViewRenderer extends Rapid
{
    protected $twig;
    protected $globalData = [];

    public function __construct()
    {
        // Define the base path for views dynamically based on the project
        $basePath = VISION_DIR .
            DIRECTORY_SEPARATOR .
            strtolower('app') .
            DIRECTORY_SEPARATOR .
            PROJECT_NAME .
            DIRECTORY_SEPARATOR .
            ucfirst('views');

        // Determine whether caching is enabled (via config or environment)
        $enableCache = filter_var(Config::get('IS_CACHED', getenv('IS_CACHED') ?: false), FILTER_VALIDATE_BOOLEAN);

        // Initialize Twig engine through the Rapid parent class
        parent::__construct($basePath, $enableCache);

        // Register common Twig helper functions
        $this->twig->addFunction(new TwigFunction('route', fn($name, $params = []) => routes($name, $params)));
        $this->twig->addFunction(new TwigFunction('partial', [$this, 'renderPartial'], ['is_safe' => ['html']]));
        $this->twig->addFunction(new TwigFunction('flash', fn() => Flasher::flash(true), ['is_safe' => ['html']]));
        $this->twig->addFunction(new TwigFunction('__', function ($key, $replace = [], $default = null) {
            return Lang::get($key, $default, $replace);
        }));

        // Register additional utility functions for masking sensitive data
        $this->twig->addFunction(new TwigFunction('sensorEmail', 'sensorEmail'));
        $this->twig->addFunction(new TwigFunction('sensorHP', 'sensorHP'));
    }

    /**
     * Render a partial view file.
     * Supports multiple extensions, prioritizing Rapid (Twig) templates first.
     *
     * @param string $name  Partial name (e.g. 'components.header')
     * @param array $data   Data to pass into the partial view
     * @return string       Rendered HTML content
     */
    public function renderPartial(string $name, array $data = [])
    {
        $baseDir = VISION_DIR
            . DIRECTORY_SEPARATOR
            . strtolower('app')
            . DIRECTORY_SEPARATOR
            . PROJECT_NAME
            . DIRECTORY_SEPARATOR
            . ucfirst('Views');

        $path = str_replace('.', DIRECTORY_SEPARATOR, $name);

        // Allowed extensions — priority order: Rapid (Twig) > PHP > Twig
        $allowedExts = [
            Requirements::getRapid(),  // ".rapid.php" pakai Twig
            Requirements::getPhp(),    // ".php" pakai PHP native
            '.twig',                   // Twig
        ];

        $mergedData = array_merge($this->globalData, $data);

        // Try to locate and render the file with one of the allowed extensions
        foreach ($allowedExts as $ext) {
            $ext = $this->ensureLeadingDot(strtolower($ext));
            $filePath = $baseDir . DIRECTORY_SEPARATOR . $path . $ext;

            if (file_exists($filePath)) {
                if ($ext === strtolower(Requirements::getRapid())) {
                    // Render using Twig (Rapid syntax)
                    try {
                        $relativePath = str_replace(DIRECTORY_SEPARATOR, '/', $path . $ext);
                        return $this->twig->render($relativePath, $mergedData);
                    } catch (\Twig\Error\LoaderError $e) {
                        Debugger::renderError($e);
                        return '';
                    }
                } else {
                    // Render with native PHP (pure native syntax support)
                    extract($mergedData, EXTR_OVERWRITE);
                    ob_start();
                    try {
                        include $filePath;
                    } catch (\Throwable $e) {
                        ob_end_clean();
                        Debugger::renderError($e);
                        return '';
                    }
                    return ob_get_clean();
                }
            }
        }
        // If no file found, display debug error
        Debugger::renderError(new \Exception("Partial not found in allowed extensions: $name"));
        return '';
    }

    /**
     * Render a complete view file.
     * Determines whether to use Rapid (Twig) or PHP rendering.
     *
     * @param string $file  The view file name
     * @param array  $data  The data to be passed into the view
     * @return string       Rendered view content
     */
    public function renderFile($file, $data)
    {
        $this->globalData = $data;
        $isRapid = $this->isRapidFile($file);
        $isPhp = $this->isPhpFile($file);

        // Try rendering as Rapid (Twig)
        if ($isRapid || !$isPhp) {
            $rapidFile = strtolower($file) . $this->ensureLeadingDot(Requirements::getRapid());

            try {
                $template = $this->twig->load($rapidFile);
                $mergedData = array_merge($this->globalData, $data);
                return $template->render($mergedData);
            } catch (\Twig\Error\LoaderError $e) {
                // Fall back to PHP rendering if Rapid file not found
            }
        }
        // Fallback: render using PHP native view
        if ($isPhp || !$isRapid) {
            $phpExt = $this->ensureLeadingDot(Requirements::getPhp());

            $fullPath = VISION_DIR .
                DIRECTORY_SEPARATOR .
                strtolower('app') .
                DIRECTORY_SEPARATOR .
                PROJECT_NAME .
                DIRECTORY_SEPARATOR .
                ucfirst('views') .
                DIRECTORY_SEPARATOR .
                ltrim($file, DIRECTORY_SEPARATOR);

            if (!str_ends_with($fullPath, $phpExt)) {
                $fullPath .= $phpExt;
            }

            if (!file_exists($fullPath)) {
                Debugger::renderError(new Exception("PHP view file not found: " . $fullPath));
                return;
            }
            // Parse Rapid-style syntax into PHP if needed
            $content = file_get_contents($fullPath);
            $parsedContent = RapidParser::parse($content);

            extract($data, EXTR_OVERWRITE);
            ob_start();

            try {
                eval('?>' . $parsedContent);
            } catch (\Throwable $e) {
                ob_end_clean();
                Debugger::renderError($e);
                return;
            }
            return ob_get_clean();
        }

        Debugger::renderError(new Exception("Unrecognized view file format: " . $file));
    }

    /**
     * Ensure that the given file extension starts with a dot.
     */
    private function ensureLeadingDot($ext)
    {
        return strpos($ext, '.') === 0 ? $ext : '.' . $ext;
    }

    /**
     * Get formatted view file path based on project and view name.
     */
    public function getViewPath($project, $view)
    {
        $viewParts = explode(SC::get('T_D'), $view);
        $viewFile = array_pop($viewParts);
        $subfolderPath = implode(DIRECTORY_SEPARATOR, $viewParts);
        return $subfolderPath . DIRECTORY_SEPARATOR . $viewFile;
    }

    /**
     * Get formatted layout file path based on project and layout name.
     */
    public function getLayoutPath($project, $layout)
    {
        $layoutParts = explode(SC::get('T_D'), $layout);
        $layoutFile = array_pop($layoutParts);
        $subfolderPath = implode(DIRECTORY_SEPARATOR, $layoutParts);
        return $subfolderPath . DIRECTORY_SEPARATOR . $layoutFile;
    }

    /**
     * Determine if a file uses Rapid (Twig) syntax.
     */
    private function isRapidFile($file)
    {
        $ext = $this->ensureLeadingDot(Requirements::getRapid());
        return substr($file, -strlen($ext)) === $ext;
    }

    /**
     * Determine if a file uses PHP syntax.
     */
    private function isPhpFile($file)
    {
        $ext = $this->ensureLeadingDot(Requirements::getPhp());
        return substr($file, -strlen($ext)) === $ext;
    }
}
