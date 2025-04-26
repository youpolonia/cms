<?php

namespace App\Http\Livewire;

use App\Models\Content;
use App\Models\ContentVersion;
use Livewire\Component;

class RestoreVersionButton extends Component
{
    public Content $content;
    public ContentVersion $version;
    public bool $confirmingRestoration = false;

    public function confirmRestoration()
    {
        $this->confirmingRestoration = true;
    }

    public string $restorationReason = '';

    protected $rules = [
        'restorationReason' => 'required|string|max:255'
    ];

    public function restore()
    {
        $this->validate();
        $this->authorize('update', $this->content);
        
        try {
            $response = Http::post(route('content.versions.restore', [
                'content' => $this->content,
                'version' => $this->version
            ]), [
                'reason' => $this->restorationReason
            ]);

            if ($response->successful()) {
                $this->emit('versionRestored');
                session()->flash('success', 'Version restored successfully');
                return redirect()->route('content.versions.show', [
                    'content' => $this->content,
                    'version' => $this->version
                ]);
            }
        } catch (\Exception $e) {
            $this->addError('restoration', 'Failed to restore version: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.restore-version-button');
    }
}