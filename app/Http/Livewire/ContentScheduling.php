<?php

namespace App\Http\Livewire;

use App\Models\Content;
use App\Models\ContentSchedule;
use App\Models\ContentVersion;
use Livewire\Component;
use Illuminate\Support\Carbon;

class ContentScheduling extends Component
{
    public Content $content;
    public $schedules;
    public $showForm = false;
    public $form = [
        'version_id' => '',
        'publish_at' => '',
        'unpublish_at' => '',
        'timezone' => 'UTC'
    ];

    protected $rules = [
        'form.version_id' => 'required|exists:content_versions,id',
        'form.publish_at' => 'required|date|after_or_equal:now',
        'form.unpublish_at' => 'nullable|date|after:publish_at',
        'form.timezone' => 'required|timezone'
    ];

    public function mount(Content $content)
    {
        $this->content = $content;
        $this->schedules = $content->schedules()
            ->with('version')
            ->orderBy('publish_at', 'desc')
            ->get();
    }

    public function render()
    {
        return view('livewire.content-scheduling', [
            'versions' => $this->content->versions()
                ->orderBy('created_at', 'desc')
                ->get()
        ]);
    }

    public function saveSchedule()
    {
        $this->validate();

        ContentSchedule::create([
            'content_id' => $this->content->id,
            'version_id' => $this->form['version_id'],
            'publish_at' => Carbon::parse($this->form['publish_at'], $this->form['timezone']),
            'unpublish_at' => $this->form['unpublish_at'] 
                ? Carbon::parse($this->form['unpublish_at'], $this->form['timezone'])
                : null,
            'timezone' => $this->form['timezone'],
            'status' => 'pending',
            'created_by' => auth()->id()
        ]);

        $this->resetForm();
        $this->mount($this->content);
    }

    public function cancelSchedule($id)
    {
        ContentSchedule::find($id)->update([
            'status' => 'cancelled',
            'metadata->cancelled_at' => now()->toDateTimeString(),
            'metadata->cancelled_by' => auth()->id()
        ]);

        $this->mount($this->content);
    }

    protected function resetForm()
    {
        $this->reset('form', 'showForm');
        $this->form['timezone'] = 'UTC';
    }
}