<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\ErrorCategory;
use App\Models\ErrorClassification;
use App\Models\ExportHistory;
use App\Services\AutoClassificationService;

class ErrorClassificationManager extends Component
{
    public $errorId;
    public $errorMessage;
    public $selectedCategory;
    public $suggestedCategories = [];
    public $showSuggestions = false;
    public $classificationNotes = '';
    public $autoClassificationResult = null;

    protected $listeners = ['classifyError' => 'loadError'];

    public function loadError($errorId, $errorMessage)
    {
        $this->errorId = $errorId;
        $this->errorMessage = $errorMessage;
        
        // Try auto-classification first
        $autoService = app(AutoClassificationService::class);
        $this->autoClassificationResult = $autoService->classify($errorMessage);
        
        if ($this->autoClassificationResult) {
            $this->selectedCategory = $this->autoClassificationResult['category']->id;
        } else {
            $this->suggestCategories();
        }
    }

    public function suggestCategories()
    {
        $this->suggestedCategories = ErrorCategory::query()
            ->where(function($query) {
                $query->where('name', 'like', "%{$this->errorMessage}%")
                    ->orWhere('description', 'like', "%{$this->errorMessage}%");
            })
            ->limit(5)
            ->get()
            ->toArray();

        $this->showSuggestions = count($this->suggestedCategories) > 0;
    }

    public function selectCategory($categoryId)
    {
        $this->selectedCategory = $categoryId;
        $this->showSuggestions = false;
        $this->autoClassificationResult = null;
    }

    public function saveClassification()
    {
        $this->validate([
            'selectedCategory' => 'required|exists:error_categories,id',
            'classificationNotes' => 'nullable|string|max:500'
        ]);

        ErrorClassification::create([
            'error_category_id' => $this->selectedCategory,
            'error_message' => $this->errorMessage,
            'classified_by' => $this->autoClassificationResult ? null : auth()->id(),
            'error_source_type' => 'export_history',
            'error_source_id' => $this->errorId,
            'auto_classified' => (bool)$this->autoClassificationResult,
            'confidence' => $this->autoClassificationResult['confidence'] ?? null
        ]);

        $this->emit('classificationSaved');
        $this->reset();
    }

    public function render()
    {
        return view('livewire.error-classification-manager', [
            'categories' => ErrorCategory::all()
        ]);
    }
}