<?php

namespace App\Http\Livewire;

use App\Models\ReportTemplate;
use App\Models\ScheduledExport;
use Livewire\Component;

class ScheduledExportManager extends Component
{
    public $templates;
    public $templateId;
    public $frequency = 'daily';
    public $time = '09:00';
    public $format = 'pdf';
    public $recipients = [];
    public $newRecipient = '';
    public $schedules = [];
    public $editingId = null;
    public $confirmingDeletionId = null;
    public $isActive = true;

    protected $rules = [
        'templateId' => 'required|exists:report_templates,id',
        'frequency' => 'required|in:daily,weekly,monthly',
        'time' => 'required|date_format:H:i',
        'format' => 'required|in:pdf,csv,excel',
        'recipients' => 'required|array',
        'recipients.*' => 'email',
        'isActive' => 'boolean'
    ];

    public function mount()
    {
        $this->templates = ReportTemplate::all();
        $this->loadSchedules();
    }

    public function loadSchedules()
    {
        $this->schedules = ScheduledExport::with('template')
            ->orderBy('next_run_at')
            ->get();
    }

    public function editSchedule($id)
    {
        $schedule = ScheduledExport::findOrFail($id);
        $this->editingId = $id;
        $this->templateId = $schedule->template_id;
        $this->frequency = $schedule->frequency;
        $this->time = $schedule->time;
        $this->format = $schedule->format;
        $this->recipients = $schedule->recipients;
        $this->isActive = $schedule->is_active;
    }

    public function cancelEdit()
    {
        $this->resetForm();
    }

    public function updateSchedule()
    {
        $this->validate();

        $schedule = ScheduledExport::findOrFail($this->editingId);
        $schedule->update([
            'template_id' => $this->templateId,
            'frequency' => $this->frequency,
            'time' => $this->time,
            'format' => $this->format,
            'recipients' => $this->recipients,
            'is_active' => $this->isActive,
            'next_run_at' => $this->calculateNextRun()
        ]);

        $this->resetForm();
        $this->loadSchedules();
    }

    public function confirmDeletion($id)
    {
        $this->confirmingDeletionId = $id;
    }

    public function deleteSchedule()
    {
        ScheduledExport::findOrFail($this->confirmingDeletionId)->delete();
        $this->confirmingDeletionId = null;
        $this->loadSchedules();
    }

    public function toggleStatus($id)
    {
        $schedule = ScheduledExport::findOrFail($id);
        $schedule->update(['is_active' => !$schedule->is_active]);
        $this->loadSchedules();
    }

    // ... (keep all existing methods like addRecipient, removeRecipient, calculateNextRun, etc.)

    public function render()
    {
        return view('livewire.scheduled-export-manager');
    }
}