<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Activity;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Notifications\RegisteredToActivityNotification;

class RegisterActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_shows_register_button_if_user_hasnt_registered_to_activity()
    {
        $user = User::factory()->create();
        $activity = Activity::factory()->create();

        $response = $this->actingAs($user)->get(route('activity.show', $activity));

        $response->assertSeeText('Register to Activity');
    }

    public function test_shows_already_registered_when_user_is_registered_to_activity()
    {
        $user = User::factory()->create();
        $activity = Activity::factory()->create();
        $user->activities()->attach($activity);

        $response = $this->actingAs($user)->get(route('activity.show', $activity));

        $response->assertSeeText('You have already registered.');
        $response->assertDontSeeText('Register to Activity');
    }

    public function test_authenticated_user_can_register_to_activity()
    {
        Notification::fake();

        $user = User::factory()->create();
        $activity = Activity::factory()->create();

        $response = $this->actingAs($user)->post(route('activities.register', $activity));

        Notification::assertSentTo($user, RegisteredToActivityNotification::class);

        $response->assertRedirect(route('my-activity.show'));

        $this->assertCount(1, $user->activities()->get());
    }

    public function test_authenticated_user_cannot_register_twice_to_activity()
    {
        Notification::fake();

        $user = User::factory()->create();
        $activity = Activity::factory()->create();

        $response = $this->actingAs($user)->post(route('activities.register', $activity));
        $response->assertRedirect(route('my-activity.show'));

        $r = $this->actingAs($user)->post(route('activities.register', $activity));
        $r->assertStatus(409);

        $this->assertCount(1, $user->activities()->get());

        Notification::assertSentTimes(RegisteredToActivityNotification::class, 1);
    }

    public function test_guest_gets_redirected_to_register_page()
    {
        $activity = Activity::factory()->create();

        $response = $this->post(route('activities.register', $activity));

        $response->assertRedirect(route('register'). '?activity=' . $activity->id);
    }
}
