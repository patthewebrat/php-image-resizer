<?php

declare(strict_types=1);

use ImageResizer\Services\CacheService;

test('generates consistent cache keys', function () {
    $config = getTestConfig();
    $cache = new CacheService($config);

    $key1 = $cache->generateKey('https://example.com/image.jpg', 100, 100, 75, 'centre');
    $key2 = $cache->generateKey('https://example.com/image.jpg', 100, 100, 75, 'centre');

    expect($key1)->toBe($key2)
        ->and($key1)->toHaveLength(64); // SHA-256 produces 64 character hex string
});

test('generates different keys for different parameters', function () {
    $config = getTestConfig();
    $cache = new CacheService($config);

    $key1 = $cache->generateKey('https://example.com/image.jpg', 100, 100, 75, 'centre');
    $key2 = $cache->generateKey('https://example.com/image.jpg', 200, 200, 75, 'centre');

    expect($key1)->not->toBe($key2);
});

test('can put and get cache data', function () {
    $config = getTestConfig();
    $cache = new CacheService($config);

    $key = $cache->generateKey('https://example.com/test.jpg', 100, 100, 75, null);
    $data = 'test image data';

    $cache->put($key, $data);

    expect($cache->has($key))->toBeTrue()
        ->and($cache->get($key))->toBe($data);
});

test('returns false for missing cache', function () {
    $config = getTestConfig();
    $cache = new CacheService($config);

    expect($cache->get('nonexistent'))->toBeFalse()
        ->and($cache->has('nonexistent'))->toBeFalse();
});

test('can delete cache entry', function () {
    $config = getTestConfig();
    $cache = new CacheService($config);

    $key = $cache->generateKey('https://example.com/test.jpg', 100, 100, 75, null);
    $cache->put($key, 'test data');

    expect($cache->has($key))->toBeTrue();

    $cache->delete($key);

    expect($cache->has($key))->toBeFalse();
});

test('can clear all cache', function () {
    $config = getTestConfig();
    $cache = new CacheService($config);

    // Create multiple cache entries
    $key1 = $cache->generateKey('https://example.com/test1.jpg', 100, 100, 75, null);
    $key2 = $cache->generateKey('https://example.com/test2.jpg', 200, 200, 75, null);

    $cache->put($key1, 'data1');
    $cache->put($key2, 'data2');

    $count = $cache->clear();

    expect($count)->toBe(2)
        ->and($cache->has($key1))->toBeFalse()
        ->and($cache->has($key2))->toBeFalse();
});

test('respects cache lifetime', function () {
    $config = getTestConfig(['cache_lifetime' => 1]);
    $cache = new CacheService($config);

    $key = $cache->generateKey('https://example.com/test.jpg', 100, 100, 75, null);
    $cache->put($key, 'test data');

    expect($cache->isValid($key))->toBeTrue();

    // Wait for cache to expire
    sleep(2);

    expect($cache->isValid($key))->toBeFalse()
        ->and($cache->has($key))->toBeFalse();
});
