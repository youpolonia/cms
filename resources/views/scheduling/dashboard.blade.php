@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>Content Scheduling Dashboard</h1>
        </div>
        <div class="col-md-4 text-right">
            <button class="btn btn-primary" data-toggle="modal" data-target="#createScheduleModal">
                <i class="fas fa-plus"></i> New Schedule
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3>Schedule Calendar</h3>
                </div>
                <div class="card-body">
                    <div id="schedulingCalendar"></div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3>Upcoming Schedules</h3>
                </div>
                <div class="card-body">
                    <div class="list-group" id="upcomingSchedulesList">
                        <!-- Will be populated via JavaScript -->
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h3>Recent Activity</h3>
                </div>
                <div class="card-body">
                    <div class="list-group" id="recentActivityList">
                        <!-- Will be populated via JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Schedule Modal -->
<div class="modal fade" id="createScheduleModal" tabindex="-1" role="dialog" aria-labelledby="createScheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createScheduleModalLabel">Create New Schedule</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="scheduleForm">
                    <div class="form-group">
                        <label for="contentSelect">Content</label>
                        <select class="form-control" id="contentSelect" required>
                            <!-- Will be populated via JavaScript -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="versionSelect">Version</label>
                        <select class="form-control" id="versionSelect" required>
                            <!-- Will be populated via JavaScript -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="publishDate">Publish Date/Time</label>
                        <input type="datetime-local" class="form-control" id="publishDate" required>
                    </div>
                    <div class="form-group">
                        <label for="unpublishDate">Unpublish Date/Time (optional)</label>
                        <input type="datetime-local" class="form-control" id="unpublishDate">
                    </div>
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="isRecurring">
                        <label class="form-check-label" for="isRecurring">Recurring Schedule</label>
                    </div>
                    <div class="form-group" id="recurrenceOptions" style="display: none;">
                        <label for="recurrenceRule">Recurrence Rule</label>
                        <input type="text" class="form-control" id="recurrenceRule" placeholder="Example: FREQ=WEEKLY;INTERVAL=2;BYDAY=MO,WE,FR">
                        <small class="form-text text-muted">Use iCalendar recurrence rules format</small>
                    </div>
                    <div class="form-group">
                        <button type="button" class="btn btn-secondary" id="checkConflictsBtn">Check for Conflicts</button>
                    </div>
                    <div id="conflictResults" style="display: none;">
                        <!-- Will show conflict results -->
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="submitScheduleBtn">Create Schedule</button>
            </div>
        </div>
    </div>
</div>

<!-- Conflict Resolution Modal -->
<div class="modal fade" id="conflictResolutionModal" tabindex="-1" role="dialog" aria-labelledby="conflictResolutionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="conflictResolutionModalLabel">Schedule Conflicts Detected</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="conflictDetails">
                    <!-- Will be populated with conflict details -->
                </div>
                <div class="mt-3">
                    <h5>Resolution Options</h5>
                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="resolutionOption" id="optionProceed" value="proceed" checked>
                            <label class="form-check-label" for="optionProceed">
                                Proceed Anyway (May cause content overlap)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="resolutionOption" id="optionReschedule" value="reschedule">
                            <label class="form-check-label" for="optionReschedule">
                                Reschedule to avoid conflicts
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="resolutionOption" id="optionCancel" value="cancel">
                            <label class="form-check-label" for="optionCancel">
                                Cancel conflicting schedules
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="resolveConflictsBtn">Resolve Conflicts</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize calendar
    const calendarEl = document.getElementById('schedulingCalendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: '/api/scheduling/upcoming',
        eventClick: function(info) {
            // Handle event click
        }
    });
    calendar.render();

    // Toggle recurrence options
    document.getElementById('isRecurring').addEventListener('change', function() {
        document.getElementById('recurrenceOptions').style.display = 
            this.checked ? 'block' : 'none';
    });

    // Load content and versions
    fetch('/api/content')
        .then(response => response.json())
        .then(data => {
            const contentSelect = document.getElementById('contentSelect');
            data.forEach(content => {
                const option = document.createElement('option');
                option.value = content.id;
                option.textContent = content.title;
                contentSelect.appendChild(option);
            });
        });

    // Load versions when content is selected
    document.getElementById('contentSelect').addEventListener('change', function() {
        const contentId = this.value;
        const versionSelect = document.getElementById('versionSelect');
        versionSelect.innerHTML = '<option value="">Loading...</option>';

        fetch(`/api/content/${contentId}/versions`)
            .then(response => response.json())
            .then(data => {
                versionSelect.innerHTML = '';
                data.forEach(version => {
                    const option = document.createElement('option');
                    option.value = version.id;
                    option.textContent = version.name;
                    versionSelect.appendChild(option);
                });
            });
    });

    // Check for conflicts
    document.getElementById('checkConflictsBtn').addEventListener('click', function() {
        const formData = {
            content_id: document.getElementById('contentSelect').value,
            publish_at: document.getElementById('publishDate').value,
            unpublish_at: document.getElementById('unpublishDate').value
        };

        fetch('/api/scheduling/check-conflicts', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            const conflictResults = document.getElementById('conflictResults');
            conflictResults.style.display = 'block';
            
            if (data.has_conflicts) {
                conflictResults.innerHTML = `
                    <div class="alert alert-warning">
                        <h5>Conflicts Detected</h5>
                        <ul>
                            ${data.conflicts.map(conflict => `<li>${conflict.message}</li>`).join('')}
                        </ul>
                        <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#conflictResolutionModal">
                            Resolve Conflicts
                        </button>
                    </div>
                `;
            } else {
                conflictResults.innerHTML = `
                    <div class="alert alert-success">
                        No scheduling conflicts detected
                    </div>
                `;
            }
        });
    });

    // Submit schedule
    document.getElementById('submitScheduleBtn').addEventListener('click', function() {
        const formData = {
            content_id: document.getElementById('contentSelect').value,
            version_id: document.getElementById('versionSelect').value,
            publish_at: document.getElementById('publishDate').value,
            unpublish_at: document.getElementById('unpublishDate').value,
            is_recurring: document.getElementById('isRecurring').checked,
            recurrence_rule: document.getElementById('isRecurring').checked 
                ? document.getElementById('recurrenceRule').value 
                : null
        };

        fetch('/api/scheduling', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                $('#createScheduleModal').modal('hide');
                calendar.refetchEvents();
                loadUpcomingSchedules();
                showToast('Schedule created successfully');
            } else {
                alert('Error: ' + data.message);
            }
        });
    });

    // Load upcoming schedules
    function loadUpcomingSchedules() {
        fetch('/api/scheduling/upcoming')
            .then(response => response.json())
            .then(data => {
                const list = document.getElementById('upcomingSchedulesList');
                list.innerHTML = '';
                
                data.today.forEach(schedule => {
                    const item = document.createElement('a');
                    item.href = '#';
                    item.className = 'list-group-item list-group-item-action';
                    item.innerHTML = `
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">${schedule.content.title}</h6>
                            <small>${new Date(schedule.publish_at).toLocaleTimeString()}</small>
                        </div>
                        <p class="mb-1">Publishing version: ${schedule.content_version.name}</p>
                    `;
                    list.appendChild(item);
                });
            });
    }

    // Initial load
    loadUpcomingSchedules();
});
</script>
@endsection