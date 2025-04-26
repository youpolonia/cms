<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class ArchiveBrowser extends Component
{
    use WithPagination;

    public $search = '';
    public $dateFilter = '';
    public $categoryFilter = '';
    public $priorityFilter = '';
    public $readStatusFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'dateFilter' => ['except' => ''],
        'categoryFilter' => ['except' => ''],
        'priorityFilter' => ['except' => ''],
        'readStatusFilter' => ['except' => '']
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $notifications = Notification::query()
            ->where('user_id', Auth::id())
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('title', 'like', '%'.$this->search.'%')
                      ->orWhere('message', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->dateFilter, function($query) {
                switch($this->dateFilter) {
                    case 'today':
                        $query->whereDate('created_at', today());
                        break;
                    case 'this_week':
                        $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                        break;
                    case 'this_month':
                        $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
                        break;
                }
            })
            ->when($this->categoryFilter, function($query) {
                $query->where('category', $this->categoryFilter);
            })
            ->when($this->priorityFilter, function($query) {
                $query->where('priority', $this->priorityFilter);
            })
            ->when($this->readStatusFilter, function($query) {
                $query->where('read_at', $this->readStatusFilter === 'read' ? '!=' : '=', null);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.archive-browser', [
            'notifications' => $notifications
        ]);
    }

    public function markAsRead($notificationId)
    {
        $notification = Notification::find($notificationId);
        if ($notification && !$notification->read_at) {
            $notification->update(['read_at' => now()]);
        }
    }

    public function markAsUnread($notificationId)
    {
        $notification = Notification::find($notificationId);
        if ($notification) {
            $notification->update(['read_at' => null]);
        }
    }
}