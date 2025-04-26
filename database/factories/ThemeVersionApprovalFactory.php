<?php

namespace Database\Factories;

use App\Models\ThemeVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

class ThemeVersionApprovalFactory extends Factory
{
    public function definition(): array
    {
        return [
            'theme_version_id' => ThemeVersion::factory(),
            'current_step' => 1,
            'progress' => 0,
            'approvers' => [],
            'settings' => [
                'notify_on_completion' => true,
                'notify_on_rejection' => true,
                'notify_on_step_change' => true
            ]
        ];
    }

    public function withSteps(int $stepCount): static
    {
        return $this->state([
            'approvers' => User::factory($stepCount)->create()->pluck('id')->toArray(),
            'settings' => [
                'required_approvals' => array_fill(0, $stepCount, 1)
            ]
        ]);
    }

    public function atStep(int $step): static
    {
        return $this->state([
            'current_step' => $step,
            'progress' => ($step - 1) / 5 * 100 // Assuming 5 steps total
        ]);
    }
}
