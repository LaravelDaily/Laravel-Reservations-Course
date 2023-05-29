<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Role;
use App\Models\Activity;
use App\Models\UserInvitation;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;
use App\Notifications\RegisteredToActivityNotification;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(Request $request): View
    {
        $email = null;

        if ($request->has('invitation_token')) {
            $token = $request->input('invitation_token');

            session()->put('invitation_token', $token);

            $invitation = UserInvitation::where('token', $token)
                ->whereNull('registered_at')
                ->firstOrFail();

            $email = $invitation->email;
        }

        if ($request->has('activity')) {
            session()->put('activity', $request->input('activity'));
        }

        return view('auth.register', compact('email'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if ($request->session()->get('invitation_token')) {
            $invitation = UserInvitation::where('token', $request->session()->get('invitation_token'))
                ->where('email', $request->email)
                ->whereNull('registered_at')
                ->firstOr(fn() => throw ValidationException::withMessages(['invitation' => 'Invitation link does not match the email']));

            $role = $invitation->role_id;
            $company = $invitation->company_id;

            $invitation->update(['registered_at' => now()]);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $role ?? Role::CUSTOMER->value,
            'company_id' => $company ?? null,
        ]);

        event(new Registered($user));

        Auth::login($user);

        $activity = Activity::find($request->session()->get('activity'));
        if ($request->session()->get('activity') && $activity) {
            $user->activities()->attach($request->session()->get('activity'));

            $user->notify(new RegisteredToActivityNotification($activity));

            return redirect()->route('my-activity.show')->with('success', 'You have successfully registered.');
        }

        return redirect(RouteServiceProvider::HOME);
    }
}
