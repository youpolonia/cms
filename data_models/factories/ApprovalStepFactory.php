<?php

namespace Database\Factories;

use App\Models\ApprovalStep;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApprovalStepFactory extends Factory
{
    protected $model = ApprovalStep::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'order' => $this->faker->numberBetween(1, 10),
            'approval_workflow_id' => null, // Will be set when creating steps
            'role_id' => 1, // Default role ID
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
