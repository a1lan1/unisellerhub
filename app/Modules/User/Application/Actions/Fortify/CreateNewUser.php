<?php

declare(strict_types=1);

namespace App\Modules\User\Application\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Modules\User\Domain\Enums\RoleEnum;
use App\Modules\User\Domain\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Throwable;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;
    use ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     *
     * @throws Throwable
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ])->validate();

        return DB::transaction(fn () => tap(User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
        ]), function (User $user): void {
            $user->assignRole(RoleEnum::SELLER->value);
        }));
    }
}
