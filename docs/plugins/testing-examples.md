# Plugin Testing Examples

## Writing Plugin Tests

```php
<?php

namespace Tests\Feature\Plugins;

use Tests\TestCase;

class ExamplePluginTest extends TestCase
{
    /** @test */
    public function it_registers_plugin_successfully()
    {
        $response = $this->get('/plugins/example-plugin/status');
        $response->assertStatus(200);
    }
}
```

## Testing Hooks

```php
/** @test */
public function it_executes_content_render_hook()
{
    $pluginService = app(PluginService::class);
    $pluginService->addHook('content.render', function($content) {
        return $content . ' [modified]';
    });

    $result = $pluginService->executeHook('content.render', 'original');
    $this->assertEquals('original [modified]', $result);
}
```

## Testing Configuration

```php
/** @test */
public function it_saves_plugin_settings()
{
    $response = $this->actingAsAdmin()
        ->post('/plugins/example-plugin/settings', [
            'api_key' => 'test123',
            'enabled' => true
        ]);

    $response->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseHas('plugin_settings', [
        'plugin' => 'example-plugin',
        'key' => 'api_key',
        'value' => 'test123'
    ]);
}
```

## Running the Test Suite

Run all plugin tests:
```bash
php artisan test tests/Feature/Plugins/
```

Run specific test class:
```bash
php artisan test tests/Feature/Plugins/ExamplePluginTest.php
```

Run with coverage:
```bash
php artisan test --coverage tests/Feature/Plugins/
```

## CI/CD Integration

Example GitHub Actions workflow:
```yaml
name: Plugin Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.1'
          extensions: mbstring, xml, ctype, json, pdo_mysql
          coverage: xdebug
      - run: composer install
      - run: php artisan test --coverage tests/Feature/Plugins/