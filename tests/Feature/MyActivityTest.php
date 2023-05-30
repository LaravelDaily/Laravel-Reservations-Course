<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Activity;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MyActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_my_activities_does_not_show_other_users_activities()
    {
        $user = User::factory()->create();
        $activity = Activity::factory()->create();
        $user->activities()->attach($activity);

        $user2 = User::factory()->create();
        $activity2 = Activity::factory()->create();
        $user2->activities()->attach($activity2);

        $response = $this->actingAs($user)->get(route('my-activity.show'));

        $response->assertSeeText($activity->name);
        $response->assertDontSeeText($activity2->name);
    }

    public function test_my_activities_shows_order_by_time_correctly()
    {
        $user = User::factory()->create();
        $activity = Activity::factory()->create(['start_time' => now()->addWeek()]);
        $activity2 = Activity::factory()->create(['start_time' => now()->addMonth()]);
        $activity3 = Activity::factory()->create(['start_time' => now()->addMonths(2)]);

        $response = $this->actingAs($user)->get(route('home'));

        $response->assertSeeTextInOrder([
            $activity->name,
            $activity2->name,
            $activity3->name,
        ]);
    }

    public function test_can_cancel_activity()
    {
        $user = User::factory()->create();
        $activity = Activity::factory()->create();
        $user->activities()->attach($activity);

        $response = $this->actingAs($user)->delete(route('my-activity.destroy', $activity));

        $response->assertRedirect(route('my-activity.show'));

        $this->assertCount(0, $user->activities()->get());
    }

    public function test_cannot_cancel_activity_for_other_user()
    {
        $user = User::factory()->create();
        $activity = Activity::factory()->create();
        $user->activities()->attach($activity);

        $user2 = User::factory()->create();

        $response = $this->actingAs($user2)->delete(route('my-activity.destroy', $activity));

        $response->assertForbidden();

        $this->assertCount(1, $user->activities()->get());
    }
}
