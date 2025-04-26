<div>
    <div class="mb-6">
        <h2 class="text-lg font-medium text-gray-900">Notification Archive</h2>
        <p class="mt-1 text-sm text-gray-600">Browse and manage your notification history.</p>
    </div>

    <div class="mb-6 grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
            <x-input-label for="search" value="Search" />
            <x-text-input 
                id="search" 
                wire:model.debounce.500ms="search" 
                type="text" 
                class="mt-1 block w-full" 
                placeholder="Search notifications..."
            />
        </div>
        <div>
            <x-input-label for="dateFilter" value="Date Range" />
            <select 
                id="dateFilter" 
                wire:model="dateFilter" 
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
            >
                <option value="">All Time</option>
                <option value="today">Today</option>
                <option value="this_week">This Week</option>
                <option value="this_month">This Month</option>
            </select>
        </div>
        <div>
            <x-input-label for="categoryFilter" value="Category" />
            <select 
                id="categoryFilter" 
                wire:model="categoryFilter" 
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
            >
                <option value="">All Categories</option>
                <option value="system">System</option>
                <option value="content">Content</option>
                <option value="user">User</option>
                <option value="approval">Approval</option>
            </select>
        </div>
        <div>
            <x-input-label for="priorityFilter" value="Priority" />
            <select 
                id="priorityFilter" 
                wire:model="priorityFilter" 
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
            >
                <option value="">All Priorities</option>
                <option value="high">High</option>
                <option value="medium">Medium</option>
                <option value="low">Low</option>
            </select>
        </div>
        <div>
            <x-input-label for="readStatusFilter" value="Status" />
            <select 
                id="readStatusFilter" 
                wire:model="readStatusFilter" 
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
            >
                <option value="">All</option>
                <option value="read">Read</option>
                <option value="unread">Unread</option>
            </select>
        </div>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <ul class="divide-y divide-gray-200">
            @forelse($notifications as $notification)
                <li class="px-4 py-4 sm:px-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            @if(!$notification->read_at)
                                <span class="h-3 w-3 rounded-full bg-blue-500 mr-3"></span>
                            @endif
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $notification->title }}
                                </p>
                                <p class="text-sm text-gray-500 mt-1">
                                    {{ $notification->message }}
                                </p>
                                <p class="text-xs text-gray-400 mt-1">
                                    {{ $notification->created_at->diffForHumans() }}
                                    • {{ ucfirst($notification->priority) }} priority
                                    • {{ ucfirst($notification->category) }}
                                </p>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            @if($notification->read_at)
                                <button 
                                    wire:click="markAsUnread('{{ $notification->id }}')" 
                                    class="text-sm text-gray-500 hover:text-gray-700"
                                >
                                    Mark Unread
                                </button>
                            @else
                                <button 
                                    wire:click="markAsRead('{{ $notification->id }}')" 
                                    class="text-sm text-blue-600 hover:text-blue-800"
                                >
                                    Mark Read
                                </button>
                            @endif
                            @if($notification->url)
                                <a 
                                    href="{{ $notification->url }}" 
                                    class="text-sm text-indigo-600 hover:text-indigo-900"
                                >
                                    View
                                </a>
                            @endif
                        </div>
                    </div>
                </li>
            @empty
                <li class="px-4 py-4 sm:px-6 text-center text-gray-500">
                    No notifications found matching your criteria.
                </li>
            @endforelse
        </ul>
    </div>

    <div class="mt-4">
        {{ $notifications->links() }}
    </div>
</div>