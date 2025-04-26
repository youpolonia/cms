@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Content Approval Analytics</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-2">Approval Rate</h3>
            <div class="text-4xl font-bold text-blue-600">85%</div>
            <div class="text-sm text-gray-500 mt-2">Last 30 days</div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-2">Average Approval Time</h3>
            <div class="text-4xl font-bold text-green-600">2.3 <span class="text-xl">days</span></div>
            <div class="text-sm text-gray-500 mt-2">Last 30 days</div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-2">Pending Approvals</h3>
            <div class="text-4xl font-bold text-orange-600">12</div>
            <div class="text-sm text-gray-500 mt-2">Currently waiting</div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Approval Timeline</h2>
        <div id="approvalTimelineChart" class="h-64"></div>
    </div>

    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">Recent Approvals</h2>
            <div class="flex space-x-2">
                <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Export Data
                </button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Content</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Approver</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">Blog Post: Getting Started</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Approved</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">John Doe</td>
                        <td class="px-6 py-4 whitespace-nowrap">2 hours ago</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">Product Page: New Feature</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">-</td>
                        <td class="px-6 py-4 whitespace-nowrap">1 day ago</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize charts and other JS functionality here
        console.log('Analytics dashboard loaded');
    });
</script>
@endpush
@endsection
