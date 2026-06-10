<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Hub;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserManagementController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    // Authorization guard — all methods require users.manage (Head only)
    // ─────────────────────────────────────────────────────────────

    private function guard(Request $request): void
    {
        abort_unless($request->user()->can('users.manage'), 403, 'Only the Head (Super Admin) can manage users.');
    }

    // ─────────────────────────────────────────────────────────────
    // List all users
    // ─────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $this->guard($request);

        $users = User::with('hub')
            ->where('id', '!=', $request->user()->id)
            ->orderBy('is_active', 'desc')
            ->orderBy('name')
            ->get();

        $hubs  = Hub::where('is_active', true)->orderBy('name')->get();
        $roles = UserRole::cases();

        return view('settings.users', compact('users', 'hubs', 'roles'));
    }

    // ─────────────────────────────────────────────────────────────
    // Create a new user with role + hub assignment
    // ─────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $this->guard($request);

        $validated = $request->validate([
            'name'           => 'required|string|max:100',
            'email'          => 'required|email|max:150|unique:users,email',
            'password'       => ['required', Password::min(8)],
            'role'           => ['required', Rule::enum(UserRole::class)],
            'hub_id'         => 'nullable|string|exists:hubs,id',
            'emp_id'         => 'nullable|string|max:50|unique:users,emp_id',
            'designation'    => 'nullable|string|max:100',
            'department'     => 'nullable|string|max:100',
            'contact_number' => 'nullable|string|max:20',
        ]);

        $role = UserRole::from($validated['role']);

        if ($role->isGlobalScope() === false && empty($validated['hub_id'])) {
            return back()->withErrors(['hub_id' => "The {$role->label()} role requires a hub assignment."])->withInput();
        }

        $user = User::create([
            'name'           => $validated['name'],
            'email'          => $validated['email'],
            'password'       => Hash::make($validated['password']),
            'role'           => $validated['role'],
            'hub_id'         => $validated['hub_id'] ?? null,
            'emp_id'         => $validated['emp_id'] ?? null,
            'designation'    => $validated['designation'] ?? null,
            'department'     => $validated['department'] ?? null,
            'contact_number' => $validated['contact_number'] ?? null,
            'is_active'      => true,
        ]);

        $user->syncRoles([$role->value]);

        return back()->with('success', "User \"{$user->name}\" created successfully.");
    }

    // ─────────────────────────────────────────────────────────────
    // Update an existing user's profile, role, hub, and status
    // ─────────────────────────────────────────────────────────────

    public function update(Request $request, User $user)
    {
        $this->guard($request);

        // Prevent Head from accidentally downgrading their own role
        if ($user->id === $request->user()->id && $request->role !== UserRole::Head->value) {
            return back()->withErrors(['role' => 'You cannot change your own role.']);
        }

        $validated = $request->validate([
            'name'           => 'required|string|max:100',
            'email'          => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'role'           => ['required', Rule::enum(UserRole::class)],
            'hub_id'         => 'nullable|string|exists:hubs,id',
            'emp_id'         => ['nullable', 'string', 'max:50', Rule::unique('users', 'emp_id')->ignore($user->id)],
            'designation'    => 'nullable|string|max:100',
            'department'     => 'nullable|string|max:100',
            'contact_number' => 'nullable|string|max:20',
            'is_active'      => 'boolean',
            'password'       => ['nullable', Password::min(8)],
        ]);

        $role = UserRole::from($validated['role']);

        if ($role->isGlobalScope() === false && empty($validated['hub_id'])) {
            return back()->withErrors(['hub_id' => "The {$role->label()} role requires a hub assignment."]);
        }

        $updateData = [
            'name'           => $validated['name'],
            'email'          => $validated['email'],
            'role'           => $validated['role'],
            'hub_id'         => $validated['hub_id'] ?? null,
            'emp_id'         => $validated['emp_id'] ?? null,
            'designation'    => $validated['designation'] ?? null,
            'department'     => $validated['department'] ?? null,
            'contact_number' => $validated['contact_number'] ?? null,
            'is_active'      => $request->boolean('is_active'),
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);
        $user->syncRoles([$role->value]);

        return back()->with('success', "User \"{$user->name}\" updated successfully.");
    }

    // ─────────────────────────────────────────────────────────────
    // Toggle is_active for a user (soft-disable without deleting)
    // ─────────────────────────────────────────────────────────────

    public function toggleActive(Request $request, User $user)
    {
        $this->guard($request);

        if ($user->id === $request->user()->id) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->update(['is_active' => !$user->is_active]);
        $state = $user->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "User \"{$user->name}\" {$state}.");
    }

    // ─────────────────────────────────────────────────────────────
    // Permanently delete a user
    // ─────────────────────────────────────────────────────────────

    public function destroy(Request $request, User $user)
    {
        $this->guard($request);

        if ($user->id === $request->user()->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $name = $user->name;
        $user->delete();

        return back()->with('success', "User \"{$name}\" deleted.");
    }
}
