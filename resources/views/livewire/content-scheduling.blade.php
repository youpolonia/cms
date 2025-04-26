<div>
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-medium">Content Scheduling</h3>
        <button @click="showForm = !showForm" class="btn btn-primary">
            {{ $showForm ? 'Cancel' : 'Add Schedule' }}
        </button>
    </div>

    @if($showForm)
        <div class="card mb-6">
            <div class="card-body">
                <form wire:submit.prevent="saveSchedule">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Version</label>
                            <select wire:model="form.version_id" class="form-select">
                                <option value="">Select Version</option>
                                @foreach($versions as $version)
                                    <option value="{{ $version->id }}">
                                        Version #{{ $version->id }} ({{ $version->created_at->format('M j, Y H:i') }})
                                    </option>
                                @endforeach
                            </select>
                            @error('form.version_id') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="form-label">Timezone</label>
                            <select wire:model="form.timezone" class="form-select">
                                @foreach(timezone_identifiers_list() as $tz)
                                    <option value="{{ $tz }}">{{ $tz }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="form-label">Publish At</label>
                            <input type="datetime-local" wire:model="form.publish_at" class="form-input" 
                                   min="{{ now()->format('Y-m-d\TH:i') }}">
                            @error('form.publish_at') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="form-label">Unpublish At (optional)</label>
                            <input type="datetime-local" wire:model="form.unpublish_at" class="form-input"
                                   min="{{ now()->addMinute()->format('Y-m-d\TH:i') }}">
                            @error('form.unpublish_at') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end">
                        <button type="submit" class="btn btn-primary">Save Schedule</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            @if($schedules->isEmpty())
                <p class="text-gray-500">No schedules found</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr>
                                <th>Version</th>
                                <th>Publish At</th>
                                <th>Unpublish At</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($schedules as $schedule)
                                <tr>
                                    <td>Version #{{ $schedule->version_id }}</td>
                                    <td>
                                        {{ $schedule->publish_at->setTimezone($schedule->timezone)->format('M j, Y H:i') }}
                                        ({{ $schedule->timezone }})
                                    </td>
                                    <td>
                                        @if($schedule->unpublish_at)
                                            {{ $schedule->unpublish_at->setTimezone($schedule->timezone)->format('M j, Y H:i') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ 
                                            match($schedule->status) {
                                                'pending' => 'warning',
                                                'published' => 'success',
                                                'unpublished' => 'info',
                                                'cancelled' => 'danger',
                                                default => 'secondary'
                                            }
                                        }}">
                                            {{ ucfirst($schedule->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($schedule->status === 'pending')
                                            <button wire:click="cancelSchedule({{ $schedule->id }})" 
                                                    class="btn btn-sm btn-danger">
                                                Cancel
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>