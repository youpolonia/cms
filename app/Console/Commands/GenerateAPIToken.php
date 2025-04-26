<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class GenerateAPIToken extends Command
{
    protected $signature = 'generate:token {--user= : User ID to generate token for} {--name=test-token : Name for the token}';
    protected $description = 'Generate a personal access token for API testing';

    public function handle()
    {
        $userId = $this->option('user');
        $user = $userId ? User::find($userId) : User::first();
        
        if (!$user) {
            $this->error($userId ? 'User not found' : 'No users found in database');
            return;
        }

        $tokenName = $this->option('name');
        $token = $user->createToken($tokenName)->plainTextToken;
        
        $this->info('API Token generated successfully:');
        $this->line($token);
    }
}
