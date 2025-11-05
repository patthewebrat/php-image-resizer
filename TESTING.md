# Testing Instructions

## Prerequisites

To run tests, you need a PHP environment with:
- PHP 8.0+
- GD extension
- Composer

## Setup

1. Install dependencies:
```bash
composer install
```

2. Create test environment file (optional):
```bash
cp .env.example .env
```

## Running Tests

### Using DDEV (Recommended)

```bash
# Start DDEV
ddev start

# Run all tests
ddev exec composer test

# Run tests with coverage
ddev exec composer test:coverage

# Run specific test file
ddev exec vendor/bin/pest tests/Unit/ConfigTest.php

# Run tests with verbose output
ddev exec vendor/bin/pest --verbose
```

### Manual Execution

```bash
# Run all tests
./vendor/bin/pest

# Run specific test file
./vendor/bin/pest tests/Unit/ConfigTest.php

# Run tests with coverage (requires xdebug)
./vendor/bin/pest --coverage

# Run tests in parallel
./vendor/bin/pest --parallel
```

## Code Quality Checks

### PHPStan (Static Analysis)

```bash
# Using DDEV
ddev exec composer phpstan

# Manual
./vendor/bin/phpstan analyse src tests --level=8
```

### PHP_CodeSniffer (Code Style)

```bash
# Check code style
ddev exec composer phpcs

# Auto-fix code style issues
ddev exec composer phpcbf
```

## Test Structure

```
tests/
├── Pest.php                    # Pest configuration
├── Unit/                       # Unit tests
│   ├── ConfigTest.php
│   ├── DomainValidatorTest.php
│   ├── ImageDownloaderTest.php
│   ├── ImageProcessorTest.php
│   └── CacheServiceTest.php
├── Integration/                # Integration tests
└── fixtures/                   # Test fixtures
    ├── cache/                  # Test cache directory
    └── images/                 # Test images
```

## Writing Tests

Example test structure:

```php
<?php

use ImageResizer\Services\SomeService;

test('can do something', function () {
    $service = new SomeService();

    $result = $service->doSomething();

    expect($result)->toBeTrue();
});

test('throws exception on invalid input', function () {
    $service = new SomeService();

    $service->doSomethingInvalid();
})->throws(InvalidArgumentException::class);
```

## Expected Test Results

All tests should pass:

```
PASS  Tests\Unit\ConfigTest
✓ can create config with custom values
✓ throws exception when allowed domains is empty
✓ throws exception when cache directory is empty
✓ throws exception when cache lifetime is zero or negative
✓ normalizes cache directory path
✓ creates cache directory if it does not exist

PASS  Tests\Unit\DomainValidatorTest
✓ validates allowed domain
✓ rejects empty url
✓ rejects invalid url format
✓ rejects disallowed domain
✓ rejects localhost
✓ rejects 127.0.0.1
✓ rejects private IP ranges
✓ isAllowed returns true for valid domains
✓ isAllowed returns false for invalid domains

PASS  Tests\Unit\ImageDownloaderTest
✓ can detect jpeg image type
✓ can detect png image type
✓ returns invalid for non-image data
✓ returns supported types

PASS  Tests\Unit\ImageProcessorTest
✓ can process and resize jpeg image
✓ can process and resize png image with transparency
✓ maintains aspect ratio when only width is provided
✓ maintains aspect ratio when only height is provided
✓ uses original dimensions when width and height are null
✓ can crop image with centre position
✓ can crop image with topleft position
✓ can output jpeg image
✓ can output png image
✓ throws exception for invalid image data
✓ throws exception for unsupported output type

PASS  Tests\Unit\CacheServiceTest
✓ generates consistent cache keys
✓ generates different keys for different parameters
✓ can put and get cache data
✓ returns false for missing cache
✓ can delete cache entry
✓ can clear all cache
✓ respects cache lifetime

Tests:  40 passed
```

## Continuous Integration

For CI/CD pipelines, run:

```bash
composer phpcs && composer phpstan && composer test
```

All checks must pass before merging.

## Troubleshooting

### Tests fail with "GD extension not found"

Install the GD extension for PHP:

```bash
# Ubuntu/Debian
apt-get install php-gd

# DDEV
ddev config --web-environment-add=PHP_EXTENSIONS=gd
ddev restart
```

### Cache permission errors

Ensure the test cache directory is writable:

```bash
chmod -R 755 tests/fixtures/cache
```

### Memory errors on large image tests

Increase PHP memory limit in php.ini or:

```bash
php -d memory_limit=512M vendor/bin/pest
```
