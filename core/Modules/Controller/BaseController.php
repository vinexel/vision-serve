<?php

/**
 * Vinexel Framework.
 *
 * @package Vision
 * @author Elwira Perdana
 * @copyright (c) PT Iconic Wira Niaga
 * @license MIT License
 */

namespace Vinexel\Modules\Controller;

use \Vision\Modules\Container;
use \Deeper\Traits\Malscan\ViewsTrait;
use \Vinexel\Modules\Debug\Debugger;

abstract class BaseController
{
    use ViewsTrait;

    /**
     * Debugger reference (class or instance depending on implementation)
     *
     * @var mixed
     */
    protected $debugger;

    /**
     * When true, skip full initialization (used for installer route)
     *
     * @var bool
     */
    protected bool $skipInitialization = false;

    /**
     * Final constructor: chooses light or full initialization depending on route.
     */
    final public function __construct()
    {
        if ($this->isInstallerRoute()) {
            $this->skipInitialization = true;
            // Optional: perform light initialization for installer pages.
            $this->lightInitialize();
        } else {
            // Perform full initialization for normal controllers.
            $this->initialize();
        }
    }

    /**
     * Detect whether current request URI is the installer route.
     *
     * @return bool
     */
    protected function isInstallerRoute(): bool
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        return strpos($uri, '/installer') === 0;
    }

    /**
     * Light initialization specifically for installer pages.
     * Keeps initialization minimal to avoid loading full app dependencies.
     */
    protected function lightInitialize(): void
    {
        // Assign the debugger class reference (not instantiated) so installer
        // code can still call static debugger helpers if needed.
        $this->debugger = Debugger::class;
    }

    /**
     * Full initialization for all controllers (except installer).
     * Child controllers may override this method if they need custom setup.
     */
    protected function initialize(): void
    {
        $this->debugger = Debugger::class;
        // Optional hook for additional debug-related initialization.
        $this->debugInitialize(); // if additional callback is needed.
    }

    /**
     * Additional logic that runs for every controller.
     * Intended as a hook; child controllers can override for extra behavior.
     */
    public function debugInitialize()
    {
        if (Debugger::isDebugMode()) {
            // Example debug log; uncomment if you want to log controller initialization.
            // Debugger::log("Controller initialized", "DEBUG");
        }
    }

    /**
     * Handle exceptions thrown inside controller methods.
     *
     * This method ensures only proper Throwable objects are processed,
     * logs the error, and renders a formatted error view for developers.
     *
     * @param \Throwable|\Exception $exception
     * @throws \Exception If the provided value is not a Throwable.
     */
    public function handleException($exception)
    {
        if (!$exception instanceof \Throwable) {
            throw new \Exception("Invalid exception type.");
        }

        // Log the error and render a nicely formatted error page/output.
        Debugger::logError($exception);
        Debugger::renderError($exception);
    }

    /**
     * ms337
     *
     * Example action that checks for the existence of the .env file
     * in the project directory. If the .env file does not exist, it will
     * call vinexel() with some default data. If the .env exists, redirect home.
     *
     * Wrapped in try/catch to ensure exceptions are handled uniformly.
     */
    public function ms337()
    {
        try {
            $envPath =
                VISION_DIR
                . DIRECTORY_SEPARATOR
                . 'app'
                . DIRECTORY_SEPARATOR
                . PROJECT_NAME
                . DIRECTORY_SEPARATOR
                . '.env';
            $data = [
                MS0 => VI,
            ];
            if (file_exists($envPath) != true) {
                // If .env does not exist, perform vinexel setup with provided data.
                $this->vinexel($data, 'vision');
            } else {
                // If .env exists, send user to the root of the application.
                header("Location: /");
                exit();
            }
        } catch (\Throwable $e) {
            $this->handleException($e);
        }
    }

    /**
     * MalScan Integrated
     *
     * IMPORTANT: Do not edit MalScan-related code in BaseController unless you
     * fully understand the security implications. Some security functions may be affected.
     *
     * These methods provide simple wrappers around the scanner() helper and
     * are protected by try/catch to ensure uniform error handling.
     */

    public function ms1()
    {
        try {
            $data = [
                MS0 => VI,
            ];
            $this->scanner(
                $data,
                MS1
            );
        } catch (\Throwable $e) {
            $this->handleException($e);
        }
    }

    public function ms2()
    {
        try {
            $data = [
                MS0 => VI,
            ];
            $this->scanner(
                $data,
                MS2
            );
        } catch (\Throwable $e) {
            $this->handleException($e);
        }
    }

    /**
     * ms3 - logs out or calls scanner with 'logout' action.
     */
    public function ms3()
    {
        try {
            $data = [
                MS0 => VI,
            ];
            $this->scanner($data, 'logout');
        } catch (\Throwable $e) {
            $this->handleException($e);
        }
    }

    /**
     * ms4 - account related scanner action.
     */
    public function ms4()
    {
        try {
            $data = [
                MS0 => VI,
            ];
            $this->scanner($data, 'account');
        } catch (\Throwable $e) {
            $this->handleException($e);
        }
    }

    /**
     * ms5 - malware scanner entry.
     */
    public function ms5()
    {
        try {
            $data = [
                MS0 => VI,
            ];
            $this->scanner($data, 'malware-scanner');
        } catch (\Throwable $e) {
            $this->handleException($e);
        }
    }

    /**
     * ms6 - security check entry.
     */
    public function ms6()
    {
        try {
            $data = [
                MS0 => VI,
            ];
            $this->scanner($data, 'security-check');
        } catch (\Throwable $e) {
            $this->handleException($e);
        }
    }

    /**
     * ms7 - install index page scanner.
     */
    public function ms7()
    {
        try {
            $data = [
                MS0 => VI,
            ];
            $this->scanner($data, 'install/index');
        } catch (\Throwable $e) {
            $this->handleException($e);
        }
    }

    /**
     * ms8 - install done page scanner.
     */
    public function ms8()
    {
        try {
            $data = [
                MS0 => VI,
            ];
            $this->scanner($data, 'install/done');
        } catch (\Throwable $e) {
            $this->handleException($e);
        }
    }
}
