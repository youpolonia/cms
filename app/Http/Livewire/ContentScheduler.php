<?php

namespace App\Http\Livewire;

use App\Models\Content;
use Carbon\Carbon;
use Livewire\Component;

class ContentScheduler extends Component
{
    public Content $content;
    public $publishAt;
    public $expireAt;
    public $isScheduled = false;

    protected $rules = [
        'publishAt' => 'nullable|date|after_or_equal:now',
        'expireAt' => 'nullable|date|after:publishAt'
    ];

    public function mount(Content $content)
    {
        $this->content = $content;
        $this->publishAt = $content->publish_at?->format('Y-m-d\TH:i');
        $this->expireAt = $content->expire_at?->format('Y-m-d\TH:i');
        $this->isScheduled = $content->is_scheduled;
    }

    public function saveSchedule()
    {
        $this->validate();

        $this->content->update([
            'publish_at' => $this->publishAt ? Carbon::parse($this->publishAt) : null,
            'expire_at' => $this->expireAt ? Carbon::parse($this->expireAt) : null,
            'is_scheduled' => $this->isScheduled
        ]);

        $this->dispatchBrowserEvent('notify', [
            'type' => 'success',
            'message' => 'Schedule saved successfully!'
        ]);
    }

    public function render()
    {
        return view('livewire.content-scheduler');
    }
}