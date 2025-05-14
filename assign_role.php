<?php

use Spatie\Permission\Models\Role;
use App\Models\User;

try {
    require __DIR__.'/vendor/autoload.php';
    $app = require_once __DIR__.'/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();

    // Verify Spatie package is installed
    if (!class_exists(Role::class)) {
        throw new Exception('Spatie Permission package not installed');
    }

    // Ensure admin role exists
    $role = Role::firstOrCreate(['name' => 'admin']);
    echo "Admin role exists (ID: {$role->id})\n";

    // Find or create test user
    // Create user with role_id set before save
    $user = new User();
    $user->name = 'Test User';
    $user->email = 'test@example.com';
    $user->password = bcrypt('password');
    $user->role_id = 1;
    $user->save();
    
    echo "User created (ID: {$user->id})\n";

    // Assign role
    $user->assignRole('admin');
    echo "Admin role assigned to test@example.com\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
