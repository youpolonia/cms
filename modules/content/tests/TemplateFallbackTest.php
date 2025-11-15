<?php
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit('Access denied');
}

namespace Modules\Content\Tests;

use PHPUnit\Framework\TestCase;
use Modules\Content\Services\TemplateResolver;

class TemplateFallbackTest extends TestCase
{
    protected TemplateResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new TemplateResolver();
    }

    public function testFindsDefaultTemplate()
    {
        $template = $this->resolver->resolve('page', 'default');
        $this->assertEquals('templates/default/page.php', $template);
    }

    public function testFindsThemeSpecificTemplate()
    {
        $template = $this->resolver->resolve('page', 'custom-theme');
        $this->assertEquals('themes/custom-theme/templates/page.php', $template);
    }

    public function testFallsBackToDefaultWhenThemeTemplateMissing()
    {
        $template = $this->resolver->resolve('missing', 'custom-theme');
        $this->assertEquals('templates/default/missing.php', $template);
    }

    public function testThrowsExceptionWhenNoTemplateFound()
    {
        $this->expectException(\RuntimeException::class);
        $this->resolver->resolve('nonexistent', 'nonexistent-theme');
    }

    public function testHandlesInvalidTemplateNames()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->resolver->resolve('../invalid', 'theme');
    }
}
