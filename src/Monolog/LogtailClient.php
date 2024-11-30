<?php

/*
 * This file is part of the logtail/monolog-logtail package.
 *
 * (c) Better Stack
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Logtail\Monolog;

use CurlHandle;
use LogicException;
use Monolog\Handler\Curl\Util;

use function curl_init;
use function curl_setopt;
use function extension_loaded;

/**
 * Format JSON records for Logtail
 */
class LogtailClient
{
    public const URL = 'https://in.logs.betterstack.com';

    public const DEFAULT_CONNECTION_TIMEOUT_MILLISECONDS = 5000;

    public const DEFAULT_TIMEOUT_MILLISECONDS = 5000;

    private string $sourceToken;

    private string $endpoint;

    private CurlHandle $handle;

    private int $connectionTimeoutMs;

    private int $timeoutMs;

    public function __construct(
        string $sourceToken,
        string $endpoint = self::URL,
        int $connectionTimeoutMs = self::DEFAULT_CONNECTION_TIMEOUT_MILLISECONDS,
        int $timeoutMs = self::DEFAULT_TIMEOUT_MILLISECONDS,
    ) {
        if (!extension_loaded('curl')) {
            throw new LogicException('The curl extension is needed to use the LogtailHandler');
        }

        $this->sourceToken = $sourceToken;
        $this->endpoint = $endpoint;
        $this->connectionTimeoutMs = $connectionTimeoutMs;
        $this->timeoutMs = $timeoutMs;
    }

    public function send($data): void
    {
        if (!isset($this->handle)) {
            $this->initCurlHandle();
        }

        curl_setopt($this->handle, CURLOPT_POSTFIELDS, $data);
        curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, true);

        Util::execute($this->handle, 5, false);
    }

    private function initCurlHandle(): void
    {
        $this->handle = curl_init();

        $headers = [
            'Content-Type: application/json',
            "Authorization: Bearer $this->sourceToken",
        ];

        curl_setopt($this->handle, CURLOPT_URL, $this->endpoint);
        curl_setopt($this->handle, CURLOPT_POST, true);
        curl_setopt($this->handle, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($this->handle, CURLOPT_CONNECTTIMEOUT_MS, $this->connectionTimeoutMs);
        curl_setopt($this->handle, CURLOPT_TIMEOUT_MS, $this->timeoutMs);
    }
}
