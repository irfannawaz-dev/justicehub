<?php

namespace App\Http\Requests;

use App\Models\CaseRecord;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $user = $this->user();
                    if ($value === $user->name) {
                        return;
                    }

                    $hasAmbiguousCurrentName = User::query()
                        ->whereKeyNot($user->id)
                        ->where('hub_id', $user->hub_id)
                        ->where('name', $user->name)
                        ->exists();
                    $nameBelongsToAnotherUser = User::query()
                        ->whereKeyNot($user->id)
                        ->where('hub_id', $user->hub_id)
                        ->where('name', $value)
                        ->exists();
                    $nameAlreadyOwnsCases = CaseRecord::query()
                        ->where('hub_id', $user->hub_id)
                        ->where('assigned_to', $value)
                        ->exists();

                    if ($hasAmbiguousCurrentName || $nameBelongsToAnotherUser || $nameAlreadyOwnsCases) {
                        $fail('This name is already used for a user or case assignment in your hub.');
                    }
                },
            ],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
        ];
    }
}
