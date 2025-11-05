# PHP Image Resizer

A modern, secure, and well-architected PHP application for resizing and caching images on-the-fly.

## Features

- **Secure**: SSRF protection, domain whitelisting, file size limits
- **Fast**: SHA-256 based caching with configurable lifetime
- **Flexible**: Support for JPEG and PNG with transparency
- **Well-tested**: Comprehensive unit tests with Pest
- **Modern**: PSR-4 autoloading, strict types, clean architecture
- **Scalable**: Separation of concerns, dependency injection ready

## Architecture

The application follows a clean, layered architecture:

```
src/
├── Config/
│   └── Config.php              # Environment configuration management
├── Services/
│   ├── DomainValidator.php     # URL/domain validation with SSRF protection
│   ├── ImageDownloader.php     # Secure image downloading
│   ├── ImageProcessor.php      # Image processing (resize/crop)
│   ├── CacheService.php        # Cache management
│   └── ImageService.php        # Orchestrates all services
├── Controllers/
│   ├── ResizeController.php    # Handles resize requests
│   └── ClearCacheController.php # Handles cache clearing
├── Exceptions/
│   └── [Custom exceptions]
└── bootstrap.php               # Application bootstrap
```

## Requirements

- PHP 8.0 or higher
- GD extension
- Composer 2.x
- DDEV (recommended for development)

## Installation

### Using DDEV (Recommended)

1. Clone the repository:
   ```bash
   git clone <repository-url>
   cd php-image-resizer
   ```

2. Start DDEV:
   ```bash
   ddev start
   ```

3. Configure environment:
   ```bash
   cp .env.example .env
   # Edit .env with your settings
   ```

4. The application is now available at the URL shown by `ddev describe`

### Manual Installation

1. Install dependencies:
   ```bash
   composer install
   ```

2. Configure environment:
   ```bash
   cp .env.example .env
   # Edit .env with your allowed domains and settings
   ```

3. Ensure the cache directory is writable:
   ```bash
   chmod 755 cache/
   ```

4. Point your web server document root to the `public/` directory

## Configuration

Edit `.env` to configure the application:

```env
# Comma-separated list of allowed domains
ALLOWED_DOMAINS=example.com,www.example.com

# Cache settings
CACHE_LIFETIME=3600           # Cache lifetime in seconds
CACHE_DIRECTORY=../cache/     # Must be writable

# Image processing
DEFAULT_QUALITY=75            # Default JPEG quality (1-100)
MAX_FILE_SIZE=10485760        # Max file size in bytes (10MB)
REQUEST_TIMEOUT=30            # HTTP request timeout in seconds
```

## Usage

### Resize an Image

```
GET /?url=<image-url>&width=<width>&height=<height>&quality=<quality>&crop=<position>
```

Or use the backward-compatible endpoint:

```
GET /resize?url=<image-url>&width=<width>&height=<height>&quality=<quality>&crop=<position>
```

**Parameters:**

- `url` (required): URL of the image to resize
- `width` (optional): Desired width in pixels
- `height` (optional): Desired height in pixels
- `quality` (optional): JPEG quality 1-100 (default: 75)
- `crop` (optional): Crop position when aspect ratios don't match

**Crop Positions:**

- `centre` (default): Center crop
- `topleft`: Crop from top-left
- `topright`: Crop from top-right
- `bottomleft`: Crop from bottom-left
- `bottomright`: Crop from bottom-right
- `topcentre`: Center horizontally, top vertically
- `bottomcentre`: Center horizontally, bottom vertically
- `centreleft`: Center vertically, left horizontally
- `centreright`: Center vertically, right horizontally

**Examples:**

```bash
# Resize to 200x200 with center crop
https://your-domain.com/?url=https://example.com/image.jpg&width=200&height=200&crop=centre

# Resize maintaining aspect ratio (width only)
https://your-domain.com/?url=https://example.com/image.jpg&width=300

# High quality resize
https://your-domain.com/?url=https://example.com/image.jpg&width=400&height=300&quality=95
```

### Clear Cache

```
GET /clear.php
```

Returns JSON with the number of files deleted.

## Development

### Running Tests

```bash
# Using DDEV
ddev exec composer test

# Or manually
composer test
```

### Code Quality

```bash
# Run PHPStan (static analysis)
ddev exec composer phpstan

# Run PHP_CodeSniffer (code style)
ddev exec composer phpcs

# Auto-fix code style issues
ddev exec composer phpcbf
```

### Running All Checks

```bash
ddev exec composer phpcs
ddev exec composer phpstan
ddev exec composer test
```

## Testing

The application includes comprehensive unit tests using Pest PHP. Tests cover:

- Configuration management
- Domain validation and SSRF protection
- Image downloading
- Image processing (resize, crop, transparency)
- Cache management
- Service integration

Run tests with:

```bash
composer test
```

For coverage report:

```bash
composer test:coverage
```

## Security Features

1. **Domain Whitelisting**: Only configured domains are allowed
2. **SSRF Protection**: Blocks localhost, private IPs, and reserved ranges
3. **File Size Limits**: Configurable maximum file size
4. **Request Timeouts**: Prevents hanging requests
5. **Input Validation**: Strict parameter validation
6. **Error Handling**: Generic errors to users, detailed logging

## API Response Headers

The application returns useful headers:

- `Content-Type`: `image/jpeg` or `image/png`
- `Cache-Control`: Max-age based on `CACHE_LIFETIME`
- `Image-Cached`: `cached` or `not-cached`

## Error Handling

Errors return appropriate HTTP status codes:

- `400`: Bad request (missing parameters, invalid quality)
- `403`: Forbidden (domain not allowed)
- `500`: Internal server error (processing failed)

Error responses are in JSON format:

```json
{
  "error": "Error message"
}
```

## Migration from Old Version

If you're upgrading from the old single-file version:

1. The old `resize.php` has been backed up to `resize.php.old`
2. `/resize` endpoint is backward compatible via `.htaccess` rewrite
3. All functionality is preserved with improved security and architecture
4. Update your `.env` file with new configuration options

## Performance

- **Caching**: SHA-256 based cache keys with configurable lifetime
- **Memory**: Efficient GD image handling with proper cleanup
- **File Locking**: Prevents duplicate processing of same image

## Contributing

1. Follow PSR-12 coding standards
2. Write tests for new features
3. Ensure all tests pass: `composer test`
4. Run static analysis: `composer phpstan`
5. Check code style: `composer phpcs`

## License

MIT License - see LICENSE file for details

## Changelog

### Version 2.0.0 - Complete Refactor

- Complete architectural refactor with proper separation of concerns
- Enhanced security (SSRF protection, input validation)
- Comprehensive unit test suite with Pest
- PSR-4 autoloading
- Dependency injection ready architecture
- PHPStan level 8 compliance
- PSR-12 code standards
- Improved error handling and logging
- Better caching strategy with SHA-256
- DDEV configuration for easy development
- Comprehensive documentation

