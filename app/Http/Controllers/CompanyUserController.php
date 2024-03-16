<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Str;
use App\Models\UserInvitation;
use Illuminate\Support\Facades\Gate;
use App\Mail\UserRegistrationInvite;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;

class CompanyUserController extends Controller
{
    public function index(Company $company)
    {
        Gate::authorize('viewAny', $company);

        $users = $company->users()->where('role_id', Role::COMPANY_OWNER)->get();

        return view('companies.users.index', compact('company', 'users'));
    }

    public function store(StoreUserRequest $request, Company $company)
    {
        Gate::authorize('create', $company);

        $invitation = UserInvitation::create([
            'email'      => $request->input('email'),
            'token'      => Str::uuid(),
            'company_id' => $company->id,
            'role_id'    => Role::COMPANY_OWNER,
        ]);

        Mail::to($request->input('email'))->send(new UserRegistrationInvite($invitation));

        return to_route('companies.users.index', $company);
    }

    public function create(Company $company)
    {
        Gate::authorize('create', $company);

        return view('companies.users.create', compact('company'));
    }

    public function edit(Company $company, User $user)
    {
        Gate::authorize('update', $company);

        return view('companies.users.edit', compact('company', 'user'));
    }

    public function update(UpdateUserRequest $request, Company $company, User $user)
    {
        Gate::authorize('update', $company);

        $user->update($request->validated());

        return to_route('companies.users.index', $company);
    }

    public function destroy(Company $company, User $user)
    {
        Gate::authorize('delete', $company);

        $user->delete();

        return to_route('companies.users.index', $company);
    }
}
