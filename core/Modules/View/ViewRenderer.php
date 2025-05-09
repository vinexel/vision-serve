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

class ViewRenderer extends Rapid
{
    protected $twig;
    protected $globalData = [];

    public function __construct()
    {
        $basePath = VISION_DIR .
            DIRECTORY_SEPARATOR .
            strtolower('app') .
            DIRECTORY_SEPARATOR .
            PROJECT_NAME .
            DIRECTORY_SEPARATOR .
            ucfirst('views');

        $enableCache = filter_var(Config::get('IS_CACHED', getenv('IS_CACHED') ?: false), FILTER_VALIDATE_BOOLEAN);

        parent::__construct($basePath, $enableCache);

        $this->twig->addFunction(new TwigFunction('route', fn($name) => routes($name)));
        $this->twig->addFunction(new TwigFunction('partial', [$this, 'renderPartial'], ['is_safe' => ['html']]));
        $this->twig->addFunction(new TwigFunction('flash', fn() => Flasher::flash(true), ['is_safe' => ['html']]));
        $this->twig->addFunction(new TwigFunction('__', function ($key, $replace = [], $default = null) {
            return Lang::get($key, $default, $replace);
        }));
    }

    public function renderPartial($name, $data = [])
    {
        $path = str_replace('.', DIRECTORY_SEPARATOR, $name);
        $basePath = "{$path}" . strtolower(Requirements::getRapid());
        $mergedData = array_merge($this->globalData, $data);

        try {
            return $this->twig->render($basePath, $mergedData);
        } catch (\Twig\Error\LoaderError $e) {
            Debugger::renderError($e);
        }
    }

    public function renderFile($file, $data)
    {
        $this->globalData = $data;
        $isRapid = $this->isRapidFile($file);
        $isPhp = $this->isPhpFile($file);

        if ($isRapid || !$isPhp) {
            $rapidFile = strtolower($file) . $this->ensureLeadingDot(Requirements::getRapid());

            try {
                $template = $this->twig->load($rapidFile);
                $mergedData = array_merge($this->globalData, $data);
                return $template->render($mergedData);
            } catch (\Twig\Error\LoaderError $e) {
                // Lanjut ke rendering file PHP
            }
        }

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

        Debugger::renderError(new Exception("Format file tidak dikenali: " . $file));
    }

    private function ensureLeadingDot($ext)
    {
        return strpos($ext, '.') === 0 ? $ext : '.' . $ext;
    }

    public function getViewPath($project, $view)
    {
        $viewParts = explode(SC::get('T_D'), $view);
        $viewFile = array_pop($viewParts);
        $subfolderPath = implode(DIRECTORY_SEPARATOR, $viewParts);
        return $subfolderPath . DIRECTORY_SEPARATOR . $viewFile;
    }

    public function getLayoutPath($project, $layout)
    {
        $layoutParts = explode(SC::get('T_D'), $layout);
        $layoutFile = array_pop($layoutParts);
        $subfolderPath = implode(DIRECTORY_SEPARATOR, $layoutParts);
        return $subfolderPath . DIRECTORY_SEPARATOR . $layoutFile;
    }

    private function isRapidFile($file)
    {
        $ext = $this->ensureLeadingDot(Requirements::getRapid());
        return substr($file, -strlen($ext)) === $ext;
    }

    private function isPhpFile($file)
    {
        $ext = $this->ensureLeadingDot(Requirements::getPhp());
        return substr($file, -strlen($ext)) === $ext;
    }
}
