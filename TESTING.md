# Testing Guide

This document describes how to run tests for the Submission Platform API.

## Prerequisites

- PHP 8.2+
- Composer
- MySQL/MariaDB (for feature tests)

## Setup

1. **Install dependencies:**
   ```bash
   composer install
   ```

2. **Configure test environment:**
   The test environment is configured in `phpunit.xml`. By default:
   - Pandora scanning is **disabled** during tests (mocked instead)
   - Database uses the default connection (configure in `.env` for testing)
   - Mail, queues, and sessions use array drivers

3. **Optional: Use SQLite for faster tests:**
   Uncomment these lines in `phpunit.xml`:
   ```xml
   <env name="DB_CONNECTION" value="sqlite"/>
   <env name="DB_DATABASE" value=":memory:"/>
   ```

## Running Tests

### Run all tests:
```bash
composer test
# or
./vendor/bin/phpunit
```

### Run specific test suites:
```bash
# Unit tests only
./vendor/bin/phpunit --testsuite=Unit

# Feature tests only
./vendor/bin/phpunit --testsuite=Feature
```

### Run specific test files:
```bash
./vendor/bin/phpunit tests/Feature/Api/ApiTokenTest.php
./vendor/bin/phpunit tests/Unit/FileScanServiceTest.php
```

### Run with coverage:
```bash
./vendor/bin/phpunit --coverage-html coverage
```

## Test Structure

```
tests/
├── Feature/
│   ├── Api/
│   │   ├── ApiTokenTest.php        # API token management tests
│   │   ├── FormApiTest.php         # Form API endpoint tests
│   │   └── SubmissionApiTest.php   # Submission API tests
│   └── ScanUploadedFilesTest.php   # File scanning middleware tests
└── Unit/
    └── FileScanServiceTest.php     # File scan service unit tests
```

## Key Test Coverage

### API Endpoints ✅
- **Token Management** (`ApiTokenTest`)
  - Create, list, update, delete tokens
  - Token authentication & authorization
  - IP restrictions
  - Token expiration
  - Usage tracking

- **Forms API** (`FormApiTest`)
  - CRUD operations for forms
  - Access control (user ownership)
  - Filtering by status/visibility
  - Validation

- **Submissions API** (`SubmissionApiTest`)
  - Create and manage submissions
  - Field validation
  - Status updates
  - Access control (form visibility)
  - Filtering

### Security ✅
- Token hashing (SHA-256)
- IP-based restrictions
- Rate limiting (tested via integration)
- Input validation
- Authorization checks

### File Scanning ✅
- **FileScanService** (`FileScanServiceTest`)
  - Malicious file detection
  - Clean file handling
  - Error handling (timeouts, service failures)
  - Temporary file cleanup
  - Unique filename generation

- **ScanUploadedFiles Middleware** (`ScanUploadedFilesTest`)
  - Blocks malicious files when enabled
  - Skips scanning when disabled
  - Respects `block_malicious` config
  - Handles multiple files
  - Graceful degradation on scan failures

## Mocking Pandora

Tests **do not require Pandora to be running**. The scan service is mocked:

```php
// Example: Mock clean file scan
$this->mock(FileScanService::class, function ($mock) {
    $mock->shouldReceive('scanFile')
        ->andReturn([
            'success' => true,
            'is_malicious' => false,
            'scan_results' => [],
        ]);
});

// Example: Mock malicious file detection
$this->mock(FileScanService::class, function ($mock) {
    $mock->shouldReceive('scanFile')
        ->andReturn([
            'success' => true,
            'is_malicious' => true,
            'scan_results' => ['antivirus' => ['clamav' => ['EICAR-Test']]],
        ]);
});
```

## Environment Variables for Testing

The following variables are set in `phpunit.xml` for the test environment:

```xml
<env name="PANDORA_ENABLED" value="false"/>
<env name="PANDORA_URL" value="http://pandora:6100"/>
<env name="PANDORA_BLOCK_MALICIOUS" value="true"/>
```

To enable Pandora scanning in specific tests, override the config:

```php
Config::set('services.pandora.enabled', true);
```

## Code Quality

### Run linting:
```bash
composer lint
# or
./vendor/bin/pint
```

### Auto-fix code style:
```bash
./vendor/bin/pint
```

## Continuous Integration

Before merging to main:

1. ✅ Run all tests: `composer test`
2. ✅ Run linting: `composer lint` or `./vendor/bin/pint`
3. ✅ Ensure no regressions in API endpoints
4. ✅ Verify file scanning works as expected

## Troubleshooting

### Database errors during tests
- Ensure migrations are up to date
- Check database connection in `.env` or use SQLite for tests

### Failed HTTP mocking
- Ensure `Illuminate\Support\Facades\Http` is used in services
- Check mock setup in test `setUp()` method

### Memory issues
- Use SQLite in-memory database for faster tests
- Run test suites separately

## Adding New Tests

1. **API Endpoint Tests**: Place in `tests/Feature/Api/`
2. **Service Tests**: Place in `tests/Unit/`
3. **Middleware Tests**: Place in `tests/Feature/`

Always mock external dependencies (Pandora, external APIs, file storage).

## Test Factories

Available factories for seeding test data:
- `UserFactory` - Create test users
- `FormFactory` - Create test forms
- `SubmissionFactory` - Create test submissions

Example usage:
```php
$user = User::factory()->create();
$form = Form::factory()->published()->public()->create(['user_id' => $user->id]);
$submission = Submission::factory()->submitted()->create(['form_id' => $form->id]);
```
