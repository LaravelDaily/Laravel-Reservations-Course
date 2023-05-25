<?php

namespace App\Http\Controllers;

use App\Models\Activity;

class HomeController extends Controller
{
    public function __invoke()
    {
        $activities = Activity::where('start_time', '>', now())
            ->orderBy('start_time')
            ->paginate(9);

        return view('home', compact('activities'));
    }
}
