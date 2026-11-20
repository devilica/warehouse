<?php

use App\Models\User;
use App\Services\AuthService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

it('rejects a password change when the current password is wrong', function () {
    $user = User::factory()->create();

    app(AuthService::class)->changePassword($user, 'wrong-password', 'new-password');
})->throws(ValidationException::class);

it('stores a new password when the current password matches', function () {
    $user = User::factory()->create();

    app(AuthService::class)->changePassword($user, 'password', 'new-password');

    expect(Hash::check('new-password', $user->fresh()->password))->toBeTrue();
});
