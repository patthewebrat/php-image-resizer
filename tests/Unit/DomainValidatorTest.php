<?php

declare(strict_types=1);

use ImageResizer\Exceptions\InvalidDomainException;
use ImageResizer\Services\DomainValidator;

test('validates allowed domain', function () {
    $config = getTestConfig();
    $validator = new DomainValidator($config);

    expect(fn() => $validator->validate('https://example.com/image.jpg'))->not->toThrow(InvalidDomainException::class);
});

test('rejects empty url', function () {
    $config = getTestConfig();
    $validator = new DomainValidator($config);

    $validator->validate('');
})->throws(InvalidDomainException::class, 'URL cannot be empty');

test('rejects invalid url format', function () {
    $config = getTestConfig();
    $validator = new DomainValidator($config);

    $validator->validate('not-a-valid-url');
})->throws(InvalidDomainException::class, 'Invalid URL format');

test('rejects disallowed domain', function () {
    $config = getTestConfig();
    $validator = new DomainValidator($config);

    $validator->validate('https://evil.com/image.jpg');
})->throws(InvalidDomainException::class, 'Domain not allowed');

test('rejects localhost', function () {
    $config = getTestConfig(['allowed_domains' => ['localhost']]);
    $validator = new DomainValidator($config);

    $validator->validate('https://localhost/image.jpg');
})->throws(InvalidDomainException::class, 'Potentially unsafe host');

test('rejects 127.0.0.1', function () {
    $config = getTestConfig(['allowed_domains' => ['127.0.0.1']]);
    $validator = new DomainValidator($config);

    $validator->validate('https://127.0.0.1/image.jpg');
})->throws(InvalidDomainException::class, 'Potentially unsafe host');

test('rejects private IP ranges', function () {
    $config = getTestConfig(['allowed_domains' => ['192.168.1.1']]);
    $validator = new DomainValidator($config);

    $validator->validate('https://192.168.1.1/image.jpg');
})->throws(InvalidDomainException::class, 'Potentially unsafe host');

test('isAllowed returns true for valid domains', function () {
    $config = getTestConfig();
    $validator = new DomainValidator($config);

    expect($validator->isAllowed('https://example.com/image.jpg'))->toBeTrue();
});

test('isAllowed returns false for invalid domains', function () {
    $config = getTestConfig();
    $validator = new DomainValidator($config);

    expect($validator->isAllowed('https://evil.com/image.jpg'))->toBeFalse();
});
