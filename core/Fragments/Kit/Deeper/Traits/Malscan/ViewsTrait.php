<?php

/**
 * Vinexel Framework.
 *
 * @package Vision
 * @author Elwira Perdana
 * @copyright (c) PT Iconic Wira Niaga
 * @license MIT License
 */

namespace Deeper\Traits\Malscan;

use \Vision\Modules\Requirements;
use \Vinexel\Modules\Debug\DebugRestrict;

trait ViewsTrait
{
    private function vinexel($data, $ContentView)
    {
        $this->view($ContentView, $data);
    }

    private function scanner($data, $ContentView)
    {
        $this->view($ContentView, $data);
    }

    public function view($view, $data = [])
    {
        extract($data, EXTR_OVERWRITE);

        require_once
            VISION_DIR
            . DIRECTORY_SEPARATOR
            . 'system'
            . DIRECTORY_SEPARATOR
            . 'vendor'
            . DIRECTORY_SEPARATOR
            . 'plugins'
            . DIRECTORY_SEPARATOR
            . 'vinexel'
            . DIRECTORY_SEPARATOR
            . 'vision-serve'
            . DIRECTORY_SEPARATOR
            . 'core'
            . DIRECTORY_SEPARATOR
            . ucfirst('modules')
            . DIRECTORY_SEPARATOR
            . ucfirst('globals')
            . DIRECTORY_SEPARATOR
            . ucfirst('malscan')
            . DIRECTORY_SEPARATOR
            . $view
            . Requirements::getRapid();
        DebugRestrict::render();
    }
}
