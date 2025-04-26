<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Notification;
use App\Exports\NotificationsExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class NotificationExporter extends Component
{
    public $exportFormat = 'csv';
    public $dateRange = 'last_month';
    public $includeRead = true;
    public $includeUnread = true;
    public $exportStatus = null;
    public $downloadUrl = null;

    protected $rules = [
        'exportFormat' => 'required|in:csv,xlsx,pdf',
        'dateRange' => 'required|in:last_week,last_month,last_quarter,custom',
        'includeRead' => 'boolean',
        'includeUnread' => 'boolean'
    ];

    public function export()
    {
        $this->validate();
        $this->exportStatus = 'processing';

        $fileName = 'notifications_export_'.now()->format('Y-m-d_His').'.'.$this->exportFormat;
        $filePath = 'exports/'.$fileName;

        $export = new NotificationsExport(
            Auth::id(),
            $this->dateRange,
            $this->includeRead,
            $this->includeUnread
        );

        Excel::store($export, $filePath, 'local');

        $this->downloadUrl = Storage::url($filePath);
        $this->exportStatus = 'completed';
    }

    public function download()
    {
        if ($this->downloadUrl) {
            return response()->download(storage_path('app/'.$this->downloadUrl))
                ->deleteFileAfterSend(true);
        }
    }

    public function render()
    {
        return view('livewire.notification-exporter');
    }
}