<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\User;
use App\Models\Activity;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreActivityRequest;
use App\Http\Requests\UpdateActivityRequest;

class ActivityController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Activity::class, 'activity');
    }

    public function index()
    {
        $activities = auth()->user()->company()
            ->with('activities')
            ->get()
            ->pluck('activities')
            ->flatten();

        return view('activities.index', compact('activities'));
    }

    public function create()
    {
        $users = User::where('company_id', auth()->user()->company_id)
            ->where('role_id', Role::GUIDE)
            ->pluck('name', 'id');

        return view('activities.create', compact('users'));
    }

    public function store(StoreActivityRequest $request)
    {
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('activities', 'public');
        }

        $activity = Activity::create($request->validated() + [
            'company_id' => auth()->user()->company_id,
            'photo' => $path ?? null,
        ]);

        $activity->participants()->sync($request->input('guides'));

        return to_route('activities.index');
    }

    public function show(Activity $activity) {}

    public function edit(Activity $activity)
    {
        $users = User::where('company_id', auth()->user()->company_id)
            ->where('role_id', Role::GUIDE)
            ->pluck('name', 'id');

        return view('activities.edit', compact('users', 'activity'));
    }

    public function update(UpdateActivityRequest $request, Activity $activity)
    {
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('activities', 'public');
            if ($activity->photo) {
                Storage::disk('public')->delete($activity->photo);
            }
        }

        $activity->update($request->validated() + [
            'photo' => $path ?? $activity->photo,
        ]);

        return to_route('activities.index');
    }

    public function destroy(Activity $activity)
    {
        $activity->delete();

        return to_route('activities.index');
    }
}
