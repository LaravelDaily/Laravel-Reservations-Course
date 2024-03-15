<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\User;
use App\Models\Company;
use App\Models\Activity;
use Illuminate\Support\Facades\Gate;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreActivityRequest;
use App\Http\Requests\UpdateActivityRequest;

class CompanyActivityController extends Controller
{
    public function index(Company $company)
    {
        Gate::authorize('viewAny', $company);

        $company->load('activities');

        return view('companies.activities.index', compact('company'));
    }

    public function create(Company $company)
    {
        Gate::authorize('create', $company);

        $guides = User::where('company_id', $company->id)
            ->where('role_id', Role::GUIDE)
            ->pluck('name', 'id');

        return view('companies.activities.create', compact('guides', 'company'));
    }

    public function store(StoreActivityRequest $request, Company $company)
    {
        Gate::authorize('create', $company);

        $filename = $this->uploadImage($request);

        Activity::create($request->validated() + [
            'company_id' => $company->id,
            'photo' => $filename,
        ]);

        return to_route('companies.activities.index', $company);
    }

    public function show(Activity $activity) {}

    public function edit(Company $company, Activity $activity)
    {
        Gate::authorize('update', $company);

        $guides = User::where('company_id', $company->id)
            ->where('role_id', Role::GUIDE)
            ->pluck('name', 'id');

        return view('companies.activities.edit', compact('guides', 'activity', 'company'));
    }

    public function update(UpdateActivityRequest $request, Company $company, Activity $activity)
    {
        Gate::authorize('update', $company);

        $filename = $this->uploadImage($request);

        $activity->update($request->validated() + [
            'photo' => $filename ?? $activity->photo,
        ]);

        return to_route('companies.activities.index', $company);
    }

    public function destroy(Company $company, Activity $activity)
    {
        Gate::authorize('delete', $company);

        $activity->delete();

        return to_route('companies.activities.index', $company);
    }

    private function uploadImage(StoreActivityRequest|UpdateActivityRequest $request): string|null
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        $filename = $request->file('image')->store(options: 'activities');

        $thumb = ImageManager::imagick()->read(Storage::disk('activities')->get($filename))
            ->scaleDown(274, 274)
            ->toJpeg()
            ->toFilePointer();

        Storage::disk('activities')->put('thumbs/' . $request->file('image')->hashName(), $thumb);

        return $filename;
    }
}
