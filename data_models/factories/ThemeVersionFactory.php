<?php

namespace Database\Factories;

use App\Models\Theme;
use App\Models\ThemeVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

class ThemeVersionFactory extends Factory
{
    protected $model = ThemeVersion::class;

    public function definition()
    {
        return [
            'theme_id' => Theme::factory(),
            'version' => $this->faker->semver(),
            'manifest' => [
                'name' => $this->faker->word(),
                'description' => $this->faker->sentence(),
                'version' => $this->faker->semver(),
                'author' => $this->faker->name(),
            ],
            'changelog' => $this->faker->paragraph(),
            'is_active' => $this->faker->boolean(),
            'parent_version_id' => null,
            'file_changes' => [],
            'diff_data' => [],
            'is_rollback' => false,
            'branch_name' => null,
            'tags' => [],
        ];
    }

    public function withParent()
    {
        return $this->state(function (array $attributes) {
            return [
                'parent_version_id' => ThemeVersion::factory(),
            ];
        });
    }

    public function asBranch(string $branchName)
    {
        return $this->state(function (array $attributes) use ($branchName) {
            return [
                'branch_name' => $branchName,
            ];
        });
    }

    public function withTags(array $tags)
    {
        return $this->state(function (array $attributes) use ($tags) {
            return [
                'tags' => $tags,
            ];
        });
    }
}
