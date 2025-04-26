<?php

namespace Tests\Unit\Services;

use App\Services\FileModificationService;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class FileModificationServiceTest extends TestCase
{
    private FileModificationService $service;
    private string $testFilePath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FileModificationService();
        $this->testFilePath = storage_path('app/test_file.txt');
        File::put($this->testFilePath, "Line 1\nLine 2\nLine 3");
    }

    protected function tearDown(): void
    {
        if (File::exists($this->testFilePath)) {
            File::delete($this->testFilePath);
        }
        parent::tearDown();
    }

    public function test_create_backup_successfully()
    {
        $backupPath = $this->service->createBackup($this->testFilePath);
        
        $this->assertNotNull($backupPath);
        $this->assertFileExists($backupPath);
        $this->assertFileEquals($this->testFilePath, $backupPath);
        
        File::delete($backupPath);
    }

    public function test_create_backup_fails_for_nonexistent_file()
    {
        $backupPath = $this->service->createBackup('/nonexistent/file.txt');
        $this->assertNull($backupPath);
    }

    public function test_apply_resolutions_with_accept_changes()
    {
        $diff = [
            'diff' => [
                [
                    'type' => 'added',
                    'line_number' => 2,
                    'line' => 'New line',
                    'applied' => true
                ],
                [
                    'type' => 'removed', 
                    'line_number' => 3,
                    'applied' => true
                ]
            ]
        ];

        $resolutions = [
            2 => 'accept',
            3 => 'accept'
        ];

        $result = $this->service->applyResolutions($this->testFilePath, $diff, $resolutions);
        $this->assertTrue($result);

        $expectedContent = "Line 1\nNew line";
        $this->assertEquals($expectedContent, File::get($this->testFilePath));
    }

    public function test_apply_resolutions_with_reject_changes()
    {
        $diff = [
            'diff' => [
                [
                    'type' => 'added',
                    'line_number' => 2,
                    'line' => 'New line',
                    'applied' => true
                ]
            ]
        ];

        $resolutions = [
            2 => 'reject'
        ];

        $result = $this->service->applyResolutions($this->testFilePath, $diff, $resolutions);
        $this->assertTrue($result);

        $expectedContent = "Line 1\nLine 2\nLine 3";
        $this->assertEquals($expectedContent, File::get($this->testFilePath));
    }

    public function test_apply_resolutions_fails_for_nonexistent_file()
    {
        $result = $this->service->applyResolutions('/nonexistent/file.txt', [], []);
        $this->assertFalse($result);
    }
}
