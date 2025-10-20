<?php

/**
 * Vinexel Framework.
 *
 * @package Vision
 * @author Elwira Perdana
 * @copyright (c) PT Iconic Wira Niaga
 * @license MIT License
 */

namespace Vinexel\Modules\Config;

use App\RegisterProjects as URL;
use GuzzleHttp\Client;

class Projects
{
    public static function getProjectName($currentDomain)
    {
        if (array_key_exists($currentDomain, URL::getDomains())) {
            return URL::getDomains()[$currentDomain];
        }

        die("Invalid domain: $currentDomain");
    }

    public static function getUri(): Client
    {
        return new Client([
            'base_uri' => WEBSITE_URI,
            'timeout'  => 10,
        ]);
    }
}
