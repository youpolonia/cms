<?php

namespace App\Http\Livewire;

use App\Models\ExportHistory;
use App\Jobs\RetryFailedExport;
use Livewire\Component;

class ErrorDetailsModal extends Component
{
    public $showModal = false;
    public $errorDetails;
    public $historyId;
    public $isRetrying = false;
    public $retryStatus = null;

    protected $listeners = ['showErrorDetails'];

    public function showErrorDetails($historyId)
    {
        $this->resetState();
        $this->historyId = $historyId;
        $record = ExportHistory::find($historyId);
        $this->errorDetails = $record->error_log;
        $this->showModal = true;
    }

    public function retryExport()
    {
        $this->isRetrying = true;
        $this->retryStatus = 'Processing...';

        $record = ExportHistory::find($this->historyId);
        
        RetryFailedExport::dispatch($record, auth()->user())
            ->onQueue('exports')
            ->afterResponse(function() {
                $this->retryStatus = 'Queued for retry';
                $this->isRetrying = false;
                $this->emit('refreshHistory');
            });
    }

    public function copyToClipboard()
    {
        $this->dispatchBrowserEvent('copy-to-clipboard', [
            'text' => $this->errorDetails
        ]);
    }

    private function resetState()
    {
        $this->isRetrying = false;
        $this->retryStatus = null;
    }

    public function render()
    {
        return view('livewire.error-details-modal');
    }
}