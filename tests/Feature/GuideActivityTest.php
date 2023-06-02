<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Activity;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GuideActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_guide_can_access_my_activities_page()
    {
        $user = User::factory()->guide()->create();

        $response = $this->actingAs($user)->get(route('guide-activity.show'));

        $response->assertOk();
    }

    public function test_other_user_cannot_access_guide_activities_page()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('guide-activity.show'));

        $response->assertForbidden();
    }

    public function test_guides_sees_activities_only_assigned_to_him()
    {
        $user = User::factory()->guide()->create();
        $activity = Activity::factory()->create(['guide_id' => $user->id]);
        $activity2 = Activity::factory()->create();

        $response = $this->actingAs($user)->get(route('guide-activity.show'));

        $response->assertSeeText($activity->name);
        $response->assertDontSeeText($activity2->name);
    }

    public function test_guide_sees_activities_ordered_by_time_correctly()
    {
        $user = User::factory()->guide()->create();
        $activity = Activity::factory()->create(['guide_id' => $user->id, 'start_time' => now()->addWeek()]);
        $activity2 = Activity::factory()->create(['guide_id' => $user->id, 'start_time' => now()->addMonth()]);
        $activity3 = Activity::factory()->create(['guide_id' => $user->id, 'start_time' => now()->addMonths(2)]);

        $response = $this->actingAs($user)->get(route('guide-activity.show'));

        $response->assertSeeTextInOrder([
            $activity->name,
            $activity2->name,
            $activity3->name,
        ]);
    }

    public function test_pdf_export()
    {
        $guide = User::factory()->guide()->create();
        $activity = Activity::factory()->create(['guide_id' => $guide->id]);

        $response = $this->actingAs($guide)->get(route('guide-activity.export', $activity));

        $this->assertNotEmpty($response->getContent());
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertEquals('attachment; filename="' . $activity->name .'.pdf"', $response->headers->get('Content-Disposition'));
    }
}
