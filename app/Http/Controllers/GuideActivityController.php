<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\Activity;
use Symfony\Component\HttpFoundation\Response;

class GuideActivityController extends Controller
{
    public function show()
    {
        abort_if(auth()->user()->role_id !== Role::GUIDE->value, Response::HTTP_FORBIDDEN);

        $activities = Activity::where('guide_id', auth()->id())->orderBy('start_time')->get();

        return view('activities.guide-activities', compact('activities'));
    }
}
