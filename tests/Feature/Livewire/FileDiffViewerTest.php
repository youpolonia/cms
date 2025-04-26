<?php

namespace Tests\Feature\Livewire;

use Tests\TestCase;
use Livewire\Livewire;
use App\Http\Livewire\FileDiffViewer;
use App\Services\FileModificationService;
use Illuminate\Support\Facades\Storage;
use Mockery;

class FileDiffViewerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_resolves_individual_conflicts()
    {
        $mockDiff = [
            'file' => 'test.txt',
            'diff' => [
                ['line_number' => 1, 'type' => 'added', 'content' => 'new line', 'applied' => null],
                ['line_number' => 2, 'type' => 'removed', 'content' => 'old line', 'applied' => null]
            ]
        ];

        Livewire::test(FileDiffViewer::class, ['diff' => $mockDiff])
            ->call('resolveConflict', ['line' => 1, 'action' => 'accept'])
            ->assertSet('resolvedLines.1', 'accept')
            ->assertSet('diff.diff.0.applied', true)
            ->call('resolveConflict', ['line' => 2, 'action' => 'reject'])
            ->assertSet('resolvedLines.2', 'reject')
            ->assertSet('diff.diff.1.applied', true)
            ->assertDispatched('resolution-applied');
    }

    public function test_bulk_actions()
    {
        $mockDiff = [
            'file' => 'test.txt',
            'diff' => [
                ['line_number' => 1, 'type' => 'added', 'content' => 'new line', 'applied' => null],
                ['line_number' => 2, 'type' => 'removed', 'content' => 'old line', 'applied' => null]
            ]
        ];

        Livewire::test(FileDiffViewer::class, ['diff' => $mockDiff])
            ->set('bulkAction', 'accept-all')
            ->call('applyBulkAction')
            ->assertSet('resolvedLines.1', 'accept')
            ->assertSet('resolvedLines.2', 'accept')
            ->assertSet('diff.diff.0.applied', true)
            ->assertSet('diff.diff.1.applied', null)
            ->assertDispatched('bulk-resolution-applied');
    }

    public function test_save_changes()
    {
        $mockDiff = [
            'file' => 'test.txt',
            'diff' => [
                ['line_number' => 1, 'type' => 'added', 'content' => 'new line', 'applied' => true],
                ['line_number' => 2, 'type' => 'removed', 'content' => 'old line', 'applied' => false]
            ]
        ];

        $this->mock(FileModificationService::class, function ($mock) {
            $mock->shouldReceive('createBackup')->once()->andReturn('backup.txt');
            $mock->shouldReceive('applyResolutions')->once()->andReturn(true);
        });

        Livewire::test(FileDiffViewer::class, ['diff' => $mockDiff])
            ->set('resolvedLines', [1 => 'accept', 2 => 'reject'])
            ->call('saveChanges')
            ->assertDispatched('save-success');
    }

    public function test_view_mode_toggle()
    {
        Livewire::test(FileDiffViewer::class)
            ->assertSet('viewMode', 'side-by-side')
            ->call('toggleViewMode')
            ->assertSet('viewMode', 'inline')
            ->call('toggleViewMode')
            ->assertSet('viewMode', 'side-by-side');
    }

    public function test_syntax_highlighting_toggle()
    {
        Livewire::test(FileDiffViewer::class)
            ->assertSet('syntaxHighlighting', true)
            ->call('toggleSyntaxHighlighting')
            ->assertSet('syntaxHighlighting', false)
            ->call('toggleSyntaxHighlighting')
            ->assertSet('syntaxHighlighting', true);
    }
}
