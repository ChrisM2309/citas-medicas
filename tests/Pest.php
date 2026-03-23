<?php

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature', 'Unit');

beforeEach(function (): void {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
    ensureBasePermissionsExist();
});

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function grantPermissions(User $user, array $permissions): User
{
    ensureBasePermissionsExist();

    foreach ($permissions as $permission) {
        Permission::findOrCreate($permission, 'web');
    }

    $user->givePermissionTo($permissions);

    return $user;
}

function createUserWithPermissions(array $permissions = []): User
{
    $user = User::factory()->create([
        'is_active' => true,
    ]);

    return grantPermissions($user, $permissions);
}

function authenticateWithPermissions(array $permissions = []): User
{
    $user = createUserWithPermissions($permissions);
    Sanctum::actingAs($user);

    return $user;
}

function nextMonday(): string
{
    return Carbon::now()->next(Carbon::MONDAY)->toDateString();
}

function ensureBasePermissionsExist(): void
{
    foreach ([
        'read_appointments',
        'read_own_appointments',
        'read_all_appointments',
        'manage_appointments',
        'manage_patients',
        'read_patients',
        'manage_users',
        'manage_medical_records',
    ] as $permission) {
        Permission::findOrCreate($permission, 'web');
    }
}
