@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-2xl font-bold">Content Scheduling Calendar</h1>
            <a href="{{ route('content.scheduling.index') }}" 
               class="px-4 py-2 bg-gray-100 rounded-md">
                List View
            </a>
        </div>

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div id="scheduling-calendar" class="p-4"></div>
        </div>
    </div>

    @push('styles')
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <style>
        .fc-event {
            cursor: pointer;
        }
        .fc-event-published {
            background-color: #10B981;
            border-color: #10B981;
        }
        .fc-event-scheduled {
            background-color: #3B82F6;
            border-color: #3B82F6;
        }
        .fc-event-recurring {
            background-color: #8B5CF6;
            border-color: #8B5CF6;
            border-style: dashed;
        }
    </style>
    @endpush

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('scheduling-calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: [
                    @foreach($events as $event)
                    {
                        title: '{{ $event['title'] }}' + (@json($event['is_recurring']) ? ' (Recurring)' : ''),
                        start: '{{ $event['start'] }}',
                        end: '{{ $event['end'] ?? '' }}',
                        url: '{{ $event['url'] }}',
                        className: @json($event['is_recurring']) ? 'fc-event-recurring' :
                                (@json($event['is_published']) ? 'fc-event-published' : 'fc-event-scheduled',
                        extendedProps: {
                            is_recurring: @json($event['is_recurring']),
                            frequency: @json($event['frequency'] ?? null)
                        }
                    },
                    @endforeach
                ],
                eventContent: function(arg) {
                    let title = arg.event.title;
                    if (arg.event.extendedProps.is_recurring) {
                        title += ' (' + arg.event.extendedProps.frequency + ')';
                    }
                    return { html: title };
                },
                eventClick: function(info) {
                    info.jsEvent.preventDefault();
                    window.location.href = info.event.url;
                }
            });
            calendar.render();
        });
    </script>
    @endpush
@endsection