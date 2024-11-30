<?php

declare(strict_types=1);

namespace Tests\Feature\Monolog;

use Logtail\Monolog\LogtailHandler;
use Logtail\Monolog\SynchronousLogtailHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
use Tests\Mocks\MockLogtailClient;

it('handler write', function (): void {
    $handler = new SynchronousLogtailHandler('sourceTokenXYZ');

    // hack: replace the private client object
    $mockClient = new MockLogtailClient();
    $setMockClient = fn () => $this->client = $mockClient;
    $setMockClient->call($handler);

    $logger = new Logger('test');
    $logger->pushHandler($handler);
    $logger->debug('test message');

    $decoded = from_json($mockClient->capturedData);

    $this->assertArrayHasKey('monolog', $decoded);
    $this->assertArrayHasKey('extra', $decoded['monolog']);

    // the introspection processor
    $this->assertArrayHasKey('file', $decoded['monolog']['extra']);
    $this->assertArrayHasKey('line', $decoded['monolog']['extra']);
    $this->assertArrayHasKey('class', $decoded['monolog']['extra']);
    $this->assertArrayHasKey('function', $decoded['monolog']['extra']);

    // the web processor
    $this->assertArrayHasKey('url', $decoded['monolog']['extra']);
    $this->assertArrayHasKey('ip', $decoded['monolog']['extra']);
    $this->assertArrayHasKey('http_method', $decoded['monolog']['extra']);
    $this->assertArrayHasKey('server', $decoded['monolog']['extra']);
    $this->assertArrayHasKey('referrer', $decoded['monolog']['extra']);

    // the process ID processor
    $this->assertArrayHasKey('process_id', $decoded['monolog']['extra']);

    // the hostname processor
    $this->assertArrayHasKey('hostname', $decoded['monolog']['extra']);
});

it('handler write with line formatter', function (): void {
    $handler = new SynchronousLogtailHandler('sourceTokenXYZ');

    // test a scenario when the formatter has been set, so the default formatter is not used
    // this is the case with e.g. Laravel
    $handler->setFormatter(new LineFormatter());

    // hack: replace the private client object
    $mockClient = new MockLogtailClient();
    $setMockClient = fn () => $this->client = $mockClient;
    $setMockClient->call($handler);

    $logger = new Logger('test');
    $logger->pushHandler($handler);
    $logger->debug('test message');

    //$decoded = from_json($mockClient->capturedData);

    $this->assertEquals(0, json_last_error(), 'The formatted data is not valid JSON');
});

it('handler write with batch write', function (): void {
    $synchronousHandler = new SynchronousLogtailHandler('sourceTokenXYZ');
    $handler = new LogtailHandler('sourceTokenXYZ');

    // hack: replace the private client object
    $mockClient = new MockLogtailClient();
    $setMockClient = fn () => $this->client = $mockClient;
    $setMockHandler = fn () => $this->handler = $synchronousHandler;

    $setMockClient->call($synchronousHandler);
    $setMockHandler->call($handler);

    $logger = new Logger('test');
    $logger->pushHandler($handler);
    $logger->debug('test message');
    $logger->debug('test message2');
    $handler->flush();

    $decoded = from_json($mockClient->capturedData);

    $this->assertEquals(0, json_last_error(), 'The formatted data is not valid JSON');

    expect($decoded)->toBeArray('Expected array of logs');

    $this->assertCount(2, $decoded, 'Expected two logs');
});
