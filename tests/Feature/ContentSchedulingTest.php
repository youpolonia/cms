<?php

namespace Tests\Feature;

use App\Models\Content;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

class ContentSchedulingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Mock authentication
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        // Create fresh in-memory database
        config(['database.connections.testing' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]]);

        config(['database.default' => 'testing']);

        // Manually create tables needed for tests
        Schema::connection('testing')->create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::connection('testing')->create('content_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->integer('version_number');
            $table->json('content_data');
            $table->text('change_description')->nullable();
            $table->timestamps();
        });

        Schema::connection('testing')->create('contents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('content');
            $table->string('content_type')->default('post');
            $table->string('status')->default('draft');
            $table->timestamp('publish_at')->nullable();
            $table->timestamp('expire_at')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->string('recurring_frequency')->nullable();
            $table->timestamp('recurring_end')->nullable();
            $table->timestamp('last_published_at')->nullable();
            $table->integer('views')->default(0);
            $table->integer('engagement_score')->default(0);
            $table->json('ai_metadata')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->json('seo_keywords')->nullable();
            $table->foreignId('user_id')->constrained();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });

    }

    protected function tearDown(): void
    {
        Schema::connection('testing')->dropIfExists('content_versions');
        Schema::connection('testing')->dropIfExists('contents');
        Schema::connection('testing')->dropIfExists('users');
        parent::tearDown();
    }

    public function test_content_publishes_at_scheduled_time()
    {
        $content = Content::factory()->create([
            'status' => 'scheduled',
            'publish_at' => now()->subMinute(),
            'user_id' => $this->user->id
        ]);

        $this->artisan('content:process-scheduled')
            ->assertExitCode(0);

        $this->assertEquals('published', $content->fresh()->status);
        $this->assertNotNull($content->fresh()->last_published_at);
    }

    public function test_content_expires_at_scheduled_time()
    {
        $content = Content::factory()->create([
            'status' => 'published',
            'expire_at' => now()->subMinute(),
            'user_id' => $this->user->id
        ]);

        $this->artisan('content:process-scheduled')
            ->assertExitCode(0);

        $this->assertEquals('expired', $content->fresh()->status);
    }

    public function test_recurring_content_creates_next_instance()
    {
        $content = Content::factory()->create([
            'status' => 'scheduled',
            'publish_at' => now()->subMinute(),
            'is_recurring' => true,
            'recurring_frequency' => 'daily',
            'user_id' => $this->user->id
        ]);

        $this->artisan('content:process-scheduled')
            ->assertExitCode(0);

        $this->assertEquals(2, Content::count());
        $nextContent = Content::latest('id')->first();
        $this->assertEquals($content->publish_at->addDay(), $nextContent->publish_at);
    }

    public function test_recurring_content_respects_end_date()
    {
        $content = Content::factory()->create([
            'status' => 'scheduled',
            'publish_at' => now()->subMinute(),
            'is_recurring' => true,
            'recurring_frequency' => 'daily',
            'recurring_end' => now()->addDay(),
            'user_id' => $this->user->id
        ]);

        // First run - should create next instance
        $this->artisan('content:process-scheduled')
            ->assertExitCode(0);
        $this->assertEquals(2, Content::count());

        // Second run - should not create another instance
        $this->artisan('content:process-scheduled')
            ->assertExitCode(0);
        $this->assertEquals(2, Content::count());
    }

    public function test_not_sent_for_future_scheduled_content()
    {
        $content = Content::factory()->create([
            'status' => 'scheduled',
            'publish_at' => now()->addHour(),
            'user_id' => $this->user->id
        ]);

        $this->artisan('content:process-scheduled')
            ->assertExitCode(0);

        $this->assertEquals('scheduled', $content->fresh()->status);
    }
}
