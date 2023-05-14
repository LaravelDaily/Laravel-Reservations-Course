<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\User;
use App\Models\Company;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;

class CompanyUserController extends Controller
{
    public function index(Company $company)
    {
        $users = $company->users()->get();

        return view('companies.users.index', compact('company', 'users'));
    }

    public function create(Company $company)
    {
        return view('companies.users.create', compact('company'));
    }

    public function store(StoreUserRequest $request, Company $company)
    {
        $company->users()->create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password')),
            'role_id' => Role::CUSTOMER,
        ]);

        return to_route('companies.users.index', $company);
    }

    public function edit(Company $company, User $user)
    {
        return view('companies.users.edit', compact('company', 'user'));
    }

    public function update(UpdateUserRequest $request, Company $company, User $user)
    {
        $user->update($request->validated());

        return to_route('companies.users.index', $company);
    }

    public function destroy(Company $company, User $user)
    {
        $user->delete();

        return to_route('companies.users.index', $company);
    }
}
