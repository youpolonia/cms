<?php

namespace Tests\Feature;

use Tests\TestCase;

class CsrfMiddlewareTest extends TestCase
{
    public function test_csrf_middleware_exists()
    {
        $this->assertTrue(class_exists(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class), 
            'CSRF middleware class does not exist');
    }

    public function test_csrf_middleware_registered_in_kernel()
    {
        $kernel = app(\App\Http\Kernel::class);
        $middlewareGroups = $kernel->getMiddlewareGroups();
        
        $this->assertContains(
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            $middlewareGroups['web'],
            'CSRF middleware not registered in web group'
        );
    }
}
