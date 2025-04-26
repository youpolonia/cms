<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Services\ExportValidator;

class ExportPreview extends Component
{
    use WithPagination;

    public $exportData = [];
    public $validationResults = [];
    public $perPage = 10;

    public function mount(array $data = [])
    {
        $this->exportData = $data;
        $this->validateData();
    }

    public function validateData()
    {
        $validator = new ExportValidator();
        $this->validationResults = [
            'structure' => $validator->validateDataStructure($this->exportData),
            'content' => $validator->validateDataContent($this->exportData),
            'schema' => $validator->validateAgainstSchema($this->exportData)
        ];
    }

    public function generateSampleData()
    {
        $this->exportData = [
            'id' => 1,
            'user_id' => 1,
            'file_path' => 'exports/analytics_1.json',
            'status' => 'completed',
            'expires_at' => now()->addDays(7)->toDateTimeString(),
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
            'data' => []
        ];

        // Generate sample data items
        for ($i = 0; $i < 50; $i++) {
            $this->exportData['data'][] = [
                'id' => $i + 1,
                'timestamp' => now()->subMinutes($i * 5)->toDateTimeString(),
                'event_type' => ['page_view', 'click', 'form_submit'][rand(0, 2)],
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'ip_address' => '192.168.1.' . rand(1, 255)
            ];
        }

        $this->validateData();
    }

    public function render()
    {
        return view('livewire.export-preview', [
            'items' => isset($this->exportData['data']) 
                ? array_slice($this->exportData['data'], ($this->page - 1) * $this->perPage, $this->perPage)
                : [],
            'validation' => $this->validationResults
        ]);
    }
}