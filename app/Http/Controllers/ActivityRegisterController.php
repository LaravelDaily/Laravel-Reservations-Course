<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Symfony\Component\HttpFoundation\Response;
use App\Notifications\RegisteredToActivityNotification;

class ActivityRegisterController extends Controller
{
    public function store(Activity $activity)
    {
        if (! auth()->check()) {
            return to_route('register', ['activity' => $activity->id]);
        }

        abort_if(auth()->user()->activities()->where('id', $activity->id)->exists(), Response::HTTP_CONFLICT);

        auth()->user()->activities()->attach($activity->id);

        auth()->user()->notify(new RegisteredToActivityNotification($activity));

        return to_route('my-activity.show')->with('success', 'You have successfully registered.');
    }
}
