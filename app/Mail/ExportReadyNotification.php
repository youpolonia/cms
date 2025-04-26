<?php

namespace App\Mail;

use App\Models\ScheduledExport;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ExportReadyNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ScheduledExport $scheduledExport,
        public $exportContent
    ) {}

    public function build()
    {
        return $this->subject("Scheduled Report: {$this->scheduledExport->template->name}")
            ->markdown('emails.export-ready', [
                'template' => $this->scheduledExport->template,
                'frequency' => $this->scheduledExport->frequency,
                'format' => $this->scheduledExport->format,
                'exportContent' => $this->exportContent
            ]);
    }
}