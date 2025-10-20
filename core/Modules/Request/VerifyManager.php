<?php

/**
 * Vinexel Framework.
 *
 * @package Vision
 * @author Elwira Perdana
 * @copyright (c) PT Iconic Wira Niaga
 * @license MIT License
 */

namespace Vinexel\Modules\Request;

use Exception;
use Vinexel\Modules\Middleware\Verificator;
use Vinexel\Modules\Services\CacheService;

class VerifyManager extends Verificator
{
    public function verifyRequest(): bool
    {
        if ($this->config['framework_license'] === 'mit') {
            return true;
        }

        if (!$this->licenseKey) {
            $this->block("License key is missing.");
        }

        if ($this->isLicenseCached()) {
            return true;
        }

        try {
            $response = $this->client->request('POST', 'license/verify?', [
                'form_params' => [
                    'license_key' => $this->licenseKey,
                ]
            ]);

            $data = json_decode($response->getBody(), true);

            if (!is_array($data) || empty($data['valid'])) {
                $this->block("Invalid response.");
            }

            CacheService::write($this->cacheFile, [
                'verified'     => true,
                'last_checked' => date('c'),
                'item_id'      => $data['item_id'] ?? null,
                'buyer'        => $data['buyer'] ?? null,
            ]);

            return true;
        } catch (Exception $e) {
            $this->block("Verification failed: " . $e->getMessage());
        }

        return false;
    }

    protected function isLicenseCached(): bool
    {
        $cache = CacheService::read($this->cacheFile);
        if (empty($cache['verified']) || empty($cache['last_checked'])) {
            return false;
        }

        $lastCheck = strtotime($cache['last_checked']);
        $daysPassed = (time() - $lastCheck) / (60 * 60 * 24);

        return $daysPassed <= 14;
    }

    protected function block(string $message): void
    {
        http_response_code(403);
        exit(json_encode([
            'success' => false,
            'message' => $message,
        ]));
    }
}
