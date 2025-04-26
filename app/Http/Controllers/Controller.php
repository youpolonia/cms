<?php

namespace App\Http\Controllers;

use OpenApi\Attributes\Info;
use OpenApi\Attributes\Server;
use OpenApi\Attributes\SecurityScheme;
use OpenApi\Attributes\Contact;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

#[Info(
    version: "1.0.0",
    title: "CMS API",
    description: "API Documentation for Content Management System",
    contact: new Contact(email: "support@cms.example.com")
)]
#[Server(url: "http://localhost:8000", description: "Local Development Server")]
#[SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT"
)]
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
