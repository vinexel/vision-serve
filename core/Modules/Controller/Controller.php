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

use \Vinexel\Modules\Debug\DebugHandler;
use \Vinexel\Modules\View\ViewRenderer;
use \Vision\Modules\Restrictions;
use \Vision\Modules\Container;

/**
 * Main Controller Class
 *
 * This class acts as the primary base for all application controllers within Vinexel.
 * It extends the BaseController to provide advanced rendering, debugging, and
 * content restriction features.
 *
 * Responsibilities:
 * - Initialize core modules (ViewRenderer, DebugHandler, Restrictions)
 * - Handle view and layout rendering
 * - Display and restrict debugging tools dynamically
 * - Provide error handling during view rendering
 */

class Controller extends BaseController
{
    /**
     * Handles rendering views and layouts.
     *
     * @var ViewRenderer
     */
    protected $viewRenderer;

    /**
     * Handles debugging display and error output.
     *
     * @var DebugHandler
     */
    protected $debugHandler;

    /**
     * Manages content restrictions and protection mechanisms.
     *
     * @var Restrictions
     */
    protected $restrictions;

    /**
     * Stores reusable view sections (optional, for future templating).
     *
     * @var array
     */
    protected $sections = [];

    /**
     * Initialize all essential controller components.
     *
     * Called automatically during construction, after BaseController::initialize().
     * Instantiates core modules required by most controllers.
     *
     * @return void
     */
    protected function initialize(): void
    {
        // Call parent initialization from BaseController.
        parent::initialize();

        // Load essential modules for rendering, debugging, and restrictions.
        $this->viewRenderer = new ViewRenderer();
        $this->debugHandler = new DebugHandler();
        $this->restrictions = new Restrictions();
    }

    /**
     * Render a complete page composed of a view and layout.
     *
     * @param string $view The view file name (without extension).
     * @param string $layout The layout file name (without extension).
     * @param array  $data Optional associative array of data passed to the view.
     * @param string|null $subfolder Optional subfolder inside the view directory.
     *
     * This method performs the following:
     *  - Locates layout and view file paths using ViewRenderer.
     *  - Renders the view into a `$content` variable.
     *  - Injects that content into the layout file.
     *  - Optionally includes 'metrix' data from the container if available.
     *  - Displays debugging tools and disables restricted key actions.
     *
     * Example:
     * ```php
     * $this->render('home/index', 'main', ['title' => 'Welcome']);
     * ```
     *
     * @return void
     */
    public function render($view, $layout, $data = [], $subfolder = null)
    {
        $project = PROJECT_NAME;

        try {
            // Resolve layout and view file paths based on the current project.
            $layoutFile = $this->viewRenderer->getLayoutPath($project, $layout);
            $viewFile = $this->viewRenderer->getViewPath($project, $view);

            // Render the main view file and capture its HTML output.
            $content = $this->viewRenderer->renderFile($viewFile, $data);

            // Optionally include global 'metrix' data from the container.
            $metrix = Container::get('metrix');
            if ($metrix) {
                $data['metrix'] = $metrix;
            }

            // Merge all data into layout context, including the rendered content.
            $layoutData = array_merge($data, ['content' => $content]);
            $output = $this->viewRenderer->renderFile($layoutFile, $layoutData);

            // Send the fully rendered page to the browser.
            echo $output;

            // if debugging mode is disabled.
            if (!$this->debugHandler->isEnabled()) {
                echo RESTRICTION .
                    '<script>
                        document.addEventListener("contextmenu", function(e) {
                            e.preventDefault();
                        }, false);
                        document.addEventListener("keydown", function(e) {
                            if (e.ctrlKey && (e.key === "u" || e.key === "s" || e.key === "p")) {
                                e.preventDefault();
                            }
                        });
                    </script>';
            }

            // Display debug panel or messages if debugging mode is active.
            $this->debugHandler->render();
        } catch (\Exception $e) {
            // Gracefully handle and display rendering errors.
            $this->handleRenderError($e);
        }
    }

    /**
     * Handle rendering exceptions gracefully.
     *
     * @param \Exception $e The caught exception during rendering.
     *
     * Displays a fallback error message on the browser and
     * shows the debug panel if enabled.
     *
     * @return void
     */
    private function handleRenderError(\Exception $e)
    {
        echo "<h1>Error Rendering Page</h1>";
        echo "<p>" . $e->getMessage() . "</p>";

        if ($this->debugHandler->isEnabled()) {
            $this->debugHandler->render();
        }
    }
}
