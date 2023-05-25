<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Activity;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ActivityShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_activity_page()
    {
        $activity = Activity::factory()->create();

        $response = $this->get(route('activity.show', $activity));

        $response->assertOk();
    }

    public function test_gets_404_for_unexisting_activity()
    {
        $response = $this->get(route('activity.show', 69));

        $response->assertNotFound();
    }
}
