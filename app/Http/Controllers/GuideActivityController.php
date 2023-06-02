<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\Activity;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class GuideActivityController extends Controller
{
    public function show()
    {
        abort_if(auth()->user()->role_id !== Role::GUIDE->value, Response::HTTP_FORBIDDEN);

        $activities = Activity::where('guide_id', auth()->id())->orderBy('start_time')->get();

        return view('activities.guide-activities', compact('activities'));
    }

    public function export(Activity $activity)
    {
        abort_if(auth()->user()->role_id !== Role::GUIDE->value, Response::HTTP_FORBIDDEN);

        $data = $activity->load(['participants' => function($query) {
            $query->orderByPivot('created_at');
        }]);

        return Pdf::loadView('activities.pdf', ['data' => $data])->download("{$activity->name}.pdf");
    }
}
