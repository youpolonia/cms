@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Theme Approval Analytics</h1>
        <div class="flex space-x-2">
            <x-export-button exportType="csv" />
            <x-export-button exportType="json" />
        </div>
    </div>

    <!-- Stats Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <x-stats-summary-card 
            title="Total Approvals" 
            value="142" 
            type="count"
            trend="up"
            trendValue="12%"
            color="indigo"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>'
        />
        
        <x-stats-summary-card 
            title="Avg. Approval Time" 
            value="3.2" 
            type="duration"
            trend="down"
            trendValue="0.5 days"
            color="green"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
        />
        
        <x-stats-summary-card 
            title="Rejection Rate" 
            value="8.5" 
            type="percentage"
            trend="down"
            trendValue="2.1%"
            color="red"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
        />
        
        <x-stats-summary-card 
            title="Pending Approvals" 
            value="7" 
            type="count"
            color="blue"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>'
        />
    </div>

    <!-- Timeline Visualization -->
    <div class="bg-white rounded-lg shadow p-4 mb-8">
        <h2 class="text-xl font-semibold mb-4">Approval Timeline</h2>
        <div class="flex space-x-4">
            <div class="w-1/2">
                <x-approval-timeline 
                    :steps="[
                        [
                            'id' => 1,
                            'name' => 'Initial Review',
                            'description' => 'Theme structure and basic requirements',
                            'started_at' => '2025-04-10 09:00:00',
                            'completed_at' => '2025-04-10 11:30:00'
                        ],
                        [
                            'id' => 2,
                            'name' => 'Design Review',
                            'description' => 'UI/UX and visual consistency check',
                            'started_at' => '2025-04-10 13:00:00',
                            'completed_at' => '2025-04-11 15:45:00'
                        ],
                        [
                            'id' => 3,
                            'name' => 'Code Quality',
                            'description' => 'Code standards and performance',
                            'started_at' => '2025-04-11 16:00:00',
                            'completed_at' => null
                        ],
                        [
                            'id' => 4,
                            'name' => 'Security Audit',
                            'description' => 'Vulnerability scanning',
                            'started_at' => null,
                            'completed_at' => null
                        ]
                    ]"
                    :currentStep="3"
                    :completedSteps="[1, 2]"
                />
            </div>
            <div class="w-1/2">
                <canvas id="timeline-chart" class="h-64"></canvas>
            </div>
        </div>
    </div>

    <!-- Additional Analytics Sections -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="bg-white rounded-lg shadow p-4">
            <h2 class="text-xl font-semibold mb-4">Approval Steps Breakdown</h2>
            <canvas id="steps-breakdown-chart" class="h-64"></canvas>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <h2 class="text-xl font-semibold mb-4">Recent Activity</h2>
            <div id="recent-activity-list" class="space-y-2"></div>
        </div>
    </div>
</div>

@push('scripts')
<script type="module">
    import ApprovalAnalyticsDashboard from '/js/components/ApprovalAnalytics/Dashboard.ts';
    new ApprovalAnalyticsDashboard();
</script>
@endpush
@endsection
