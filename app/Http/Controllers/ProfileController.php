<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\CaseRecord;
use App\Models\Staff;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $oldName = $user->name;
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        DB::transaction(function () use ($user, $oldName): void {
            if ($user->isDirty('name')) {
                $newName = $user->name;
                $staffIds = Staff::query()
                    ->where('user_id', $user->id)
                    ->pluck('id');

                CaseRecord::query()
                    ->where('hub_id', $user->hub_id)
                    ->where(function ($query) use ($oldName, $staffIds): void {
                        $query->where(function ($legacy) use ($oldName): void {
                            $legacy->whereNull('assigned_staff_id')
                                ->where('assigned_to', $oldName);
                        });

                        if ($staffIds->isNotEmpty()) {
                            $query->orWhereIn('assigned_staff_id', $staffIds);
                        }
                    })
                    ->update(['assigned_to' => $newName]);

                Staff::query()
                    ->where('user_id', $user->id)
                    ->update(['name' => $newName]);
            }

            $user->save();
        });

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
