<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        // set global $_SERVER data
        global $_SERVER;

        $_SERVER = array_merge($_SERVER, [
            'REQUEST_URI' => '',
            'REMOTE_ADDR' => '',
            'REQUEST_METHOD' => '',
            'SERVER_NAME' => '',
            'HTTP_REFERER' => '',
        ]);

        parent::setUp();
    }
}
