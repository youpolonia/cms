<?php

namespace Tests\Feature\Livewire;

use App\Livewire\ContentVersionComparison;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class ContentVersionComparisonTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(ContentVersionComparison::class)
            ->assertStatus(200);
    }
}
