<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

class VerifyRoutes extends Command
{
    protected $signature = 'routes:verify';
    protected $description = 'Verify all routes are accessible and clear cache if needed';

    public function handle()
    {
        $this->info('Verifying routes...');
        
        // Get all named routes
        $routes = Route::getRoutes()->getRoutesByName();
        
        $failed = [];
        foreach ($routes as $name => $route) {
            try {
                $response = app()->handle(
                    app('request')->create($route->uri())
                );
                
                if ($response->getStatusCode() !== 200) {
                    $failed[$name] = $route->uri();
                }
            } catch (\Exception $e) {
                $failed[$name] = $route->uri();
            }
        }

        if (!empty($failed)) {
            $this->warn('Some routes failed verification:');
            foreach ($failed as $name => $uri) {
                $this->line(" - $name ($uri)");
            }
            
            $this->info('Attempting to fix by clearing route cache...');
            Artisan::call('route:clear');
            
            $this->info('Route cache cleared. Please verify routes again.');
            return 1;
        }

        $this->info('All routes verified successfully!');
        return 0;
    }
}
