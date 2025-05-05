<?php

/**
 * Vinexel Framework
 *
 * @package Vision
 * @author Elwira Perdana
 * @copyright (c) PT Iconic Wira Niaga
 * @license MIT License
 */

namespace Vinexel\Modules\Middleware;

use Vinexel\Modules\Config\Projects;

class Verificator extends Projects
{
    protected $config;
    protected $client;
    protected $licenseKey;
    protected $cacheFile;

    public function __construct()
    {
        $this->config = $this->loadConfig();
        $this->client = $this->getUri();
        $this->licenseKey = $this->config['license_key'];
        $this->cacheFile = VISION_DIR . '/system/storage/license/cache.json';
    }

    protected function loadConfig(): array
    {
        $configPath = VISION_DIR . '/system/framework/Fragments/Config/License.php';
        $config = is_file($configPath) ? require $configPath : [];

        return [
            'framework_license' => $config['framework_license'] ?? (defined('FRAMEWORK_LICENSE') ? FRAMEWORK_LICENSE : null),
            'item_id'      => $config['item_id'] ?? null,
            'license_key'       => $config['license_key'] ?? null,
        ];
    }
}
