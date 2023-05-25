<?php

namespace App\Http\Controllers;

use App\Models\Activity;

class ActivityController extends Controller
{
    public function show(Activity $activity)
    {
        return view('activities.show', compact('activity'));
    }
}
