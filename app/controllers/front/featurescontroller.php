<?php
declare(strict_types=1);

namespace App\Controllers\Front;

use Core\Request;

class FeaturesController
{
    public function index(Request $request): void
    {
        render('front/features', []);
    }
}
