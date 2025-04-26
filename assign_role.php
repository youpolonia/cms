<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Role;
use App\Models\User;

// Ensure admin role exists
Role::firstOrCreate(['name' => 'admin']);

// Assign to test user
$user = User::where('email', 'test@example.com')->first();
$user->assignRole('admin');

echo "Admin role assigned to test@example.com\n";
