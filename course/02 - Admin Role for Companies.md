After creating the companies CRUD, the next step is to give access to this CRUD only for users with the `Administrator` role. Because only administrators will be able to access companies for this we will create a [Middleware](https://laravel.com/docs/middleware).

---

## Permissions

So, first, we need to create the middleware and register it. We will call it `isAdmin`.

```sh
php artisan make:middleware isAdmin
```

**app/Http/Kernel.php**:
```php
class Kernel extends HttpKernel
{
    // ...
    protected $middlewareAliases = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \App\Http\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'isAdmin' => \App\Http\Middleware\isAdmin::class, // [tl! ++]
    ];
}
```

In the middleware, we will abort the request if the user doesn't have an `administrator` role.

**App/Http/Middleware/isAdmin**:
```php
use Symfony\Component\HttpFoundation\Response;

class isAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_if($request->user()->role_id !== 1, Response::HTTP_FORBIDDEN);

        return $next($request);
    }
}
```

And we need to add this middleware to the companies route.

**routes/web.php**:
```php
// ...
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('companies', CompanyController::class); // [tl! --]
    Route::resource('companies', CompanyController::class)->middleware('isAdmin'); // [tl! ++]
});

require __DIR__.'/auth.php';
```

Now if you would visit the companies page you will get a `Forbidden` page.

![](images/companies-forbidden.png)

Next, we need to hide `Companies` in the navigations for everyone except for users with the `administrator` role. We could create a custom [Blade Directive](https://laravel.com/docs/blade#extending-blade) but for now, we will just use a simple [`@if`](https://laravel.com/docs/blade#if-statements).

Later, if we will see that we will are repeating this check then we will create a dedicated Blade directive. 

**resources/views/layouts/navigation.blade.php**:
```blade
// ...
<!-- Navigation Links -->
<div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
        {{ __('Dashboard') }}
    </x-nav-link>
    @if(auth()->user()->role_id === 1) {{-- [tl! add:start] --}}
        <x-nav-link :href="route('companies.index')" :active="request()->routeIs('companies.index')">
            {{ __('Companies') }}
        </x-nav-link>
    @endif {{-- [tl! add:end] --}}
</div>
// ...
```

So now other users don't see the `Companies` in the navigation.

![](images/companies-link-only-for-admins.png)

## Tests

Now, let's write tests to ensure that only users with the `administrator` role can access the `companies` page. But, before that, we need to fix default Breeze tests.

When we added the `role_id` column to the `Users` table it broke the default breeze tests.

```
FAILED  Tests\Feature\Auth\AuthenticationTest > users can authenticate using the login screen                                                                   QueryException   
  SQLSTATE[23000]: Integrity constraint violation: 19 NOT NULL constraint failed: users.role_id (Connection: sqlite, SQL: insert into "users" ("name", "email", "email_verified_at", "password", "remember_token", "updated_at", "created_at") values (Dolores Sauer, schmidt.bill@example.com, 2023-05-12 07:33:43, $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi, 0kYt26ciDo, 2023-05-12 07:33:43, 2023-05-12 07:33:43))

  at vendor/laravel/framework/src/Illuminate/Database/Connection.php:578
    574▕             $this->bindValues($statement, $this->prepareBindings($bindings));
    575▕ 
    576▕             $this->recordsHaveBeenModified();
    577▕ 
  ➜ 578▕             return $statement->execute();
    579▕         });
    580▕     }
    581▕ 
    582▕     /**

      +15 vendor frames 
  16  tests/Feature/Auth/AuthenticationTest.php:23
```

To fix it we just need to add `role_id` to the `UserFactory`. And while we are at the `UserFactory` let's add a [Factory State](https://laravel.com/docs/eloquent-factories#factory-states) for easier creation of an `administrator` user.

**database/factories/UserFactory.php**:
```php
class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'role_id' => 3, // [tl! ++]
        ];
    }
    // ...
    public function admin(): static // [tl! add:start]
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => 1,
        ]);
    } // [tl! add:end]
}
```

Now the tests passes.

```
Tests:    24 passed (56 assertions)
Duration: 1.07s
```

So now, we can create our tests.

```sh
php artisan make:test CompanyTest
```

**tests/Feature/CompanyTest.php**:
```php
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_user_can_access_companies_index_page(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get(route('companies.index'));

        $response->assertOk();
    }

    public function test_non_admin_user_can_access_companies_index_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('companies.index'));

        $response->assertForbidden();
    }
}
```

So, what do we do in these tests?

- First, because in the tests we are working with the database, it is important to use the `RefreshDatabase` trait.
- Next, we create a user. Here, in the first test, we use the earlier added `admin` state.
- Then, using created user we go to the `companies.index` route.
- Ant last, we check the response. The administrator will receive a response status of `200` and other users will receive a status of `403`.

```
> php artisan test --filter=CompanyTest

PASS  Tests\Feature\CompanyTest
✓ admin user can access companies index page                                                                                                                               0.09s  
✓ non admin user can access companies index page                                                                                                                           0.01s  

Tests:    2 passed (2 assertions)
Duration: 0.13s
```
