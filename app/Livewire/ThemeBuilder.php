<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Theme;
use App\Services\OpenAIService;
use App\Services\ThemeService;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Livewire component for AI-powered theme generation and versioning
 */
class ThemeBuilder extends Component
{
    public Theme $theme;
    public string $prompt = '';
    public array $generatedTheme = [];
    public bool $isGenerating = false;
    public ?string $error = null;

    protected array $rules = [
        'prompt' => 'required|min:10|max:500'
    ];

    protected array $messages = [
        'prompt.required' => 'A theme description is required',
        'prompt.min' => 'Description must be at least 10 characters',
        'prompt.max' => 'Description cannot exceed 500 characters'
    ];

    public function mount(Theme $theme): void
    {
        $this->theme = $theme;
    }

    /**
     * Generate a new theme version using AI
     */
    public function generateTheme(): void
    {
        try {
            $this->validate();
            $this->isGenerating = true;
            $this->error = null;
            $this->generatedTheme = [];

            $response = app(OpenAIService::class)->generateTheme([
                'prompt' => $this->prompt,
                'base_theme' => $this->theme->toArray()
            ]);

            $this->generatedTheme = [
                'name' => $response['name'] ?? 'Generated Theme',
                'description' => $response['description'] ?? '',
                'config' => $response['config'] ?? [],
                'assets' => $response['assets'] ?? []
            ];

        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Theme generation failed', [
                'error' => $e->getMessage(),
                'theme_id' => $this->theme->id
            ]);
            $this->error = 'Failed to generate theme. Please try again.';
        } finally {
            $this->isGenerating = false;
        }
    }

    /**
     * Save the generated theme as a new version
     */
    public function saveTheme(): void
    {
        try {
            if (empty($this->generatedTheme)) {
                throw new \RuntimeException('No theme generated to save');
            }

            $themeService = app(ThemeService::class);
            $version = $themeService->createVersion(
                $this->theme,
                $this->generatedTheme,
                'AI Generated from prompt: ' . $this->prompt
            );

            $this->dispatch('theme-saved', versionId: $version->id);
            session()->flash('message', 'Theme version created successfully!');
            
        } catch (\Exception $e) {
            Log::error('Theme save failed', [
                'error' => $e->getMessage(),
                'theme_id' => $this->theme->id
            ]);
            $this->error = 'Failed to save theme version. Please try again.';
        }
    }

    public function render()
    {
        return view('livewire.theme-builder');
    }
}
