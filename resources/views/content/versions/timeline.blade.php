@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Version History Timeline: {{ $content->title }}</h5>
                    <a href="{{ route('content.versions.index', $content) }}" class="btn btn-sm btn-outline-secondary">
                        Back to Versions
                    </a>
                </div>

                <div class="card-body">
                    <!-- Analytics Dashboard -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6>Version Comparison Analytics</h6>
                            <form id="versionFilterForm" class="d-flex gap-2">
                                <select class="form-select form-select-sm" name="branch">
                                    <option value="">All Branches</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ request('branch') == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <select class="form-select form-select-sm" name="status">
                                    <option value="">All Statuses</option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                                <select id="tagFilter" class="form-select form-select-sm" name="tags[]" multiple>
                                    @foreach($allTags as $tag)
                                        <option value="{{ $tag }}" {{ in_array($tag, request('tags', [])) ? 'selected' : '' }}>
                                            {{ $tag }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                        <div class="chart-container" style="position: relative; height:300px;">
                            <canvas id="versionAnalyticsChart" 
                                data-chart-data="{{ json_encode($chartData) }}"></canvas>
                        </div>
                    </div>

                    <!-- Timeline -->
                    <div class="timeline-container">
                        @foreach($versions as $version)
                            <div class="timeline-item">
                                <div class="timeline-item-header d-flex justify-content-between">
                                    <div>
                                        <h6 class="mb-1">
                                            Version #{{ $version->version_number }}
                                            @if($version->branch)
                                                <span class="badge bg-secondary ms-2">{{ $version->branch->name }}</span>
                                            @endif
                                        </h6>
                                        <small class="text-muted">
                                            Created {{ $version->created_at->diffForHumans() }} by {{ $version->user->name }}
                                        </small>
                                    </div>
                                    <div>
                                        @if($version->status === 'approved')
                                            <span class="badge bg-success">Approved</span>
                                        @elseif($version->status === 'rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        @endif
                                    </div>
                                </div>

                                @if(isset($versionStats[$version->id]))
                                    <div class="timeline-item-stats mt-2">
                                        <div class="progress mb-2" style="height: 20px;">
                                            <div class="progress-bar bg-success" 
                                                style="width: {{ $versionStats[$version->id]['added'] }}%" 
                                                title="Lines Added">
                                                {{ $versionStats[$version->id]['added'] }}
                                            </div>
                                            <div class="progress-bar bg-danger" 
                                                style="width: {{ $versionStats[$version->id]['removed'] }}%" 
                                                title="Lines Removed">
                                                {{ $versionStats[$version->id]['removed'] }}
                                            </div>
                                            <div class="progress-bar bg-warning text-dark" 
                                                style="width: {{ $versionStats[$version->id]['changed'] }}%" 
                                                title="Lines Changed">
                                                {{ $versionStats[$version->id]['changed'] }}
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between small">
                                            <span>
                                                <i class="fas fa-plus text-success"></i> Added: {{ $versionStats[$version->id]['added'] }}
                                            </span>
                                            <span>
                                                <i class="fas fa-minus text-danger"></i> Removed: {{ $versionStats[$version->id]['removed'] }}
                                            </span>
                                            <span>
                                                <i class="fas fa-exchange-alt text-warning"></i> Changed: {{ $versionStats[$version->id]['changed'] }}
                                            </span>
                                            <span>
                                                <i class="fas fa-exclamation-triangle text-info"></i> Conflicts: {{ $versionStats[$version->id]['conflicts'] }}
                                            </span>
                                        </div>
                                    </div>
                                @endif

                                <div class="timeline-item-actions mt-3 d-flex justify-content-between">
                                    <div>
                                        @if($version->tags)
                                            @foreach($version->tags as $tag)
                                                <span class="badge bg-info me-1">{{ $tag }}</span>
                                            @endforeach
                                        @endif
                                    </div>
                                    <div class="d-flex gap-2">
                                        @if(!$loop->first)
                                            <a href="{{ route('content.versions.compare', [
                                                'content' => $content,
                                                'version1' => $versions[$loop->index]->id,
                                                'version2' => $version->id
                                            ]) }}" 
                                               class="btn btn-sm btn-outline-secondary">
                                                Compare with Previous
                                            </a>
                                        @endif
                                        <a href="{{ route('content.versions.show', [$content, $version]) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            View Details
                                        </a>
                                        @can('restore', [$version, $content])
                                            <a href="{{ route('content.versions.rollback', [$content, $version]) }}" 
                                               class="btn btn-sm btn-outline-danger rollback-btn"
                                               data-version="{{ $version->version_number }}"
                                               data-date="{{ $version->created_at->format('M j, Y') }}"
                                               data-author="{{ $version->user->name }}">
                                                Rollback
                                            </a>
                                        @endcan
                                    </div>
                                </div>

                                <!-- Version Metadata -->
                                <div class="version-metadata mt-2">
                                    <div class="d-flex justify-content-between small text-muted">
                                        <span title="Created at">
                                            <i class="far fa-clock"></i> 
                                            {{ $version->created_at->format('M j, Y H:i') }}
                                        </span>
                                        <span title="Author">
                                            <i class="far fa-user"></i> 
                                            {{ $version->user->name }}
                                        </span>
                                        <span title="Change Type">
                                            <i class="fas fa-tag"></i> 
                                            {{ $version->change_type ?? 'Update' }}
                                        </span>
                                    </div>
                                </div>

                                @if(isset($version->diff_summary) && $version->diff_summary['ai_summary'])
                                    <div class="mt-3 p-3 bg-gray-50 rounded">
                                        <h6 class="text-sm font-medium mb-1">AI Summary of Changes</h6>
                                        <p class="text-sm">{{ $version->diff_summary['ai_summary'] }}</p>
                                    </div>
                                @endif
                            </div>
                            @if(!$loop->last)
                                <div class="timeline-connector"></div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .timeline-container {
        position: relative;
        padding-left: 30px;
    }
    .timeline-item {
        position: relative;
        padding: 15px;
        margin-bottom: 15px;
        background: #fff;
        border-radius: 5px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .timeline-item-header {
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }
    .timeline-connector {
        position: absolute;
        left: 15px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #dee2e6;
    }
    .timeline-item:before {
        content: '';
        position: absolute;
        left: -20px;
        top: 20px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #0d6efd;
        z-index: 1;
    }
    .progress-bar {
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
    }
    .version-metadata {
        padding: 8px;
        background-color: #f8f9fa;
        border-radius: 4px;
        font-size: 0.85rem;
    }
    .timeline-item:hover {
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transform: translateY(-2px);
        transition: all 0.2s ease;
    }
    .user-avatar {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        margin-right: 5px;
    }
    .version-tooltip {
        position: absolute;
        z-index: 100;
        background: white;
        padding: 10px;
        border-radius: 4px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        max-width: 300px;
        display: none;
    }
</style>
@endpush

@push('scripts')
    <script src="{{ asset('js/version-timeline.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush
