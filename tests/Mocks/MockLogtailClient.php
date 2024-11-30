<?php

declare(strict_types=1);

namespace Tests\Mocks;

use Logtail\Monolog\LogtailClient;

class MockLogtailClient extends LogtailClient
{
    public $capturedData;

    public function __construct()
    {
        parent::__construct('test-source-token');
    }

    public function send($data): void
    {
        $this->capturedData = $data;
    }
}
