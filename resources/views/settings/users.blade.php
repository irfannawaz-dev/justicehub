<x-layouts.app>
@php
    $authUser = auth()->user();

    // Role badge styles
    $roleStyle = [
        'head'                   => 'background: rgba(22,48,41,0.12); color: var(--forest);',
        'hub-admin'              => 'background: rgba(74,122,92,0.14); color: var(--moss);',
        'data-entry'             => 'background: rgba(195,145,60,0.14); color: var(--ochre);',
        'me-lead'                => 'background: rgba(138,46,29,0.10); color: var(--burgundy);',
        'complaint-investigator' => 'background: rgba(139,90,43,0.12); color: #8b5a2b;',
        'viewer'                 => 'background: rgba(0,0,0,0.06); color: var(--ink-3);',
    ];

    // Stats
    $totalUsers   = $users->count();
    $activeUsers  = $users->where('is_active', true)->count();
    $byRole       = $users->groupBy(fn($u) => $u->role->value)->map->count();

    // Roles that require a hub assignment
    $hubRequiredRoles  = ['hub-admin', 'data-entry', 'complaint-investigator'];
    $hubOptionalRoles  = ['viewer'];
    $noHubRoles        = ['head', 'me-lead'];

    // JSON for JS (hub options)
    $hubsJson  = $hubs->map(fn($h) => ['id' => $h->id, 'name' => $h->name])->values()->toJson();
    $rolesJson = collect($roles)->map(fn($r) => ['value' => $r->value, 'label' => $r->label()])->values()->toJson();
@endphp

<div style="padding: 28px 36px 60px; max-width: 1200px; margin: 0 auto;">

    {{-- ─── Header ─────────────────────────────────────────────────── --}}
    <div style="margin-bottom: 28px; padding-bottom: 22px; border-bottom: 1px solid var(--rule);">
        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; flex-wrap: wrap;">
            <div>
                <div class="label-cap" style="font-size: 9.5px; margin-bottom: 8px;">System · User Management</div>
                <h2 class="serif" style="font-size: 30px; font-weight: 400; letter-spacing: -0.02em; margin: 0 0 8px 0; color: var(--ink);">
                    Users &amp; Access
                </h2>
                <p style="font-size: 13.5px; color: var(--ink-3); line-height: 1.55; margin: 0; max-width: 680px;">
                    Create accounts, assign roles and hub access, reset passwords, and activate or deactivate users. Role determines what each user can see and do across the system.
                </p>
            </div>
            <button
                type="button"
                onclick="jhOpenModal('create-user')"
                class="btn-primary"
                style="padding: 10px 20px; font-size: 13px; display: flex; align-items: center; gap: 7px; white-space: nowrap; flex-shrink: 0;"
            >
                <x-lucide-user-plus style="width: 14px; height: 14px;" />
                Add User
            </button>
        </div>
    </div>

    {{-- ─── Stats bar ───────────────────────────────────────────────── --}}
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 24px; max-width: 920px;">
        @foreach([
            ['label' => 'Total Users',   'value' => $totalUsers,  'icon' => 'users'],
            ['label' => 'Active',        'value' => $activeUsers, 'icon' => 'user-check'],
            ['label' => 'Inactive',      'value' => $totalUsers - $activeUsers, 'icon' => 'user-x'],
            ['label' => 'Roles in use',  'value' => $byRole->count(), 'icon' => 'shield'],
        ] as $stat)
        <div class="card" style="padding: 16px 18px; display: flex; align-items: center; gap: 12px;">
            <div style="width: 32px; height: 32px; background: var(--parchment); border: 1px solid var(--rule-2); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <x-dynamic-component :component="'lucide-' . $stat['icon']" style="width: 14px; height: 14px; color: var(--forest);" />
            </div>
            <div>
                <div class="serif" style="font-size: 22px; font-weight: 500; color: var(--ink); line-height: 1.1;">{{ $stat['value'] }}</div>
                <div style="font-size: 10.5px; color: var(--ink-4); margin-top: 2px; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 500;">{{ $stat['label'] }}</div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ─── Flash messages ─────────────────────────────────────────── --}}
    @if(session('success'))
    <div style="padding: 10px 14px; background: rgba(74,122,92,0.12); border: 1px solid var(--moss); color: var(--moss); font-size: 12.5px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; max-width: 1100px;">
        <x-lucide-check-circle style="width: 14px; height: 14px; flex-shrink: 0;" />
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div style="padding: 10px 14px; background: rgba(138,46,29,0.08); border: 1px solid var(--burgundy); color: var(--burgundy); font-size: 12.5px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; max-width: 1100px;">
        <x-lucide-alert-circle style="width: 14px; height: 14px; flex-shrink: 0;" />
        {{ session('error') }}
    </div>
    @endif
    @if($errors->any())
    <div style="padding: 10px 14px; background: rgba(138,46,29,0.08); border: 1px solid var(--burgundy); color: var(--burgundy); font-size: 12.5px; margin-bottom: 16px; max-width: 1100px;">
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
            <x-lucide-alert-circle style="width: 14px; height: 14px; flex-shrink: 0;" />
            <strong>Please correct the following:</strong>
        </div>
        <ul style="margin: 4px 0 0 22px; padding: 0;">
            @foreach($errors->all() as $e)
            <li style="font-size: 12px; margin-top: 3px;">{{ $e }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- ─── Toolbar: search + filters ─────────────────────────────── --}}
    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 14px; flex-wrap: wrap; max-width: 1100px;">
        <input
            type="text"
            id="jh-user-search"
            placeholder="Search by name or email…"
            class="inp"
            style="flex: 1; min-width: 200px; max-width: 320px; font-size: 12.5px; padding: 7px 10px;"
            oninput="jhFilterUsers()"
        />
        <select id="jh-role-filter" class="inp" style="font-size: 12.5px; padding: 7px 10px; min-width: 160px;" onchange="jhFilterUsers()">
            <option value="">All roles</option>
            @foreach($roles as $r)
            <option value="{{ $r->value }}">{{ $r->label() }}</option>
            @endforeach
        </select>
        <select id="jh-hub-filter" class="inp" style="font-size: 12.5px; padding: 7px 10px; min-width: 140px;" onchange="jhFilterUsers()">
            <option value="">All hubs</option>
            @foreach($hubs as $hub)
            <option value="{{ $hub->id }}">{{ $hub->name }}</option>
            @endforeach
            <option value="none">No hub assigned</option>
        </select>
        <select id="jh-status-filter" class="inp" style="font-size: 12.5px; padding: 7px 10px; min-width: 120px;" onchange="jhFilterUsers()">
            <option value="">All statuses</option>
            <option value="1">Active only</option>
            <option value="0">Inactive only</option>
        </select>
        <div style="margin-left: auto; font-size: 11.5px; color: var(--ink-4);">
            <span id="jh-visible-count">{{ $totalUsers }}</span> user{{ $totalUsers !== 1 ? 's' : '' }}
        </div>
    </div>

    {{-- ─── Users table ─────────────────────────────────────────────── --}}
    <div style="border: 1px solid var(--rule); background: var(--paper); max-width: 1100px;">
        <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
            <thead>
                <tr style="border-bottom: 1px solid var(--rule); background: var(--parchment);">
                    <th style="padding: 10px 16px; text-align: left; font-size: 9.5px; letter-spacing: 0.06em; text-transform: uppercase; color: var(--ink-3); font-weight: 600;">User</th>
                    <th style="padding: 10px 12px; text-align: left; font-size: 9.5px; letter-spacing: 0.06em; text-transform: uppercase; color: var(--ink-3); font-weight: 600; width: 180px;">Role</th>
                    <th style="padding: 10px 12px; text-align: left; font-size: 9.5px; letter-spacing: 0.06em; text-transform: uppercase; color: var(--ink-3); font-weight: 600; width: 160px;">Hub</th>
                    <th style="padding: 10px 12px; text-align: center; font-size: 9.5px; letter-spacing: 0.06em; text-transform: uppercase; color: var(--ink-3); font-weight: 600; width: 80px;">Status</th>
                    <th style="padding: 10px 12px; width: 130px;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $u)
                <tr
                    data-user-row
                    data-user-name="{{ strtolower($u->name) }}"
                    data-user-email="{{ strtolower($u->email) }}"
                    data-user-role="{{ $u->role->value }}"
                    data-user-hub="{{ $u->hub_id ?? 'none' }}"
                    data-user-active="{{ $u->is_active ? '1' : '0' }}"
                    style="border-bottom: 1px solid var(--rule-2); {{ !$u->is_active ? 'opacity: 0.6;' : '' }}"
                >
                    {{-- Name / Email / EmpID --}}
                    <td style="padding: 12px 16px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 32px; height: 32px; background: {{ $u->is_active ? 'var(--forest)' : 'var(--ink-4)' }}; color: var(--cream); display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 500; flex-shrink: 0; letter-spacing: 0.03em;">
                                {{ strtoupper(substr($u->name, 0, 1)) }}{{ strtoupper(substr(explode(' ', $u->name)[1] ?? '', 0, 1)) }}
                            </div>
                            <div>
                                <div style="font-size: 13.5px; font-weight: 500; color: var(--ink);">
                                    {{ $u->name }}
                                    @if($u->id === $authUser->id)
                                    <span style="font-size: 9px; font-weight: 600; letter-spacing: 0.04em; padding: 1px 5px; background: rgba(195,145,60,0.14); color: var(--ochre); text-transform: uppercase; margin-left: 4px; vertical-align: middle;">You</span>
                                    @endif
                                </div>
                                <div style="font-size: 11.5px; color: var(--ink-3); margin-top: 2px;">{{ $u->email }}</div>
                                @if($u->emp_id || $u->designation)
                                <div class="mono" style="font-size: 10px; color: var(--ink-4); margin-top: 2px;">
                                    {{ implode(' · ', array_filter([$u->emp_id, $u->designation])) }}
                                </div>
                                @endif
                            </div>
                        </div>
                    </td>

                    {{-- Role badge --}}
                    <td style="padding: 12px;">
                        <span style="font-size: 10.5px; font-weight: 600; letter-spacing: 0.03em; padding: 3px 8px; text-transform: uppercase; {{ $roleStyle[$u->role->value] ?? '' }}">
                            {{ $u->role->label() }}
                        </span>
                    </td>

                    {{-- Hub --}}
                    <td style="padding: 12px;">
                        @if($u->hub)
                        <div style="font-size: 12.5px; color: var(--ink-2);">{{ $u->hub->name }}</div>
                        <div class="mono" style="font-size: 10px; color: var(--ink-4); margin-top: 1px;">{{ $u->hub_id }}</div>
                        @elseif($u->role->isGlobalScope() === true)
                        <span style="font-size: 11px; color: var(--ink-4); font-style: italic;">All hubs</span>
                        @else
                        <span style="font-size: 11px; color: var(--ink-4);">—</span>
                        @endif
                    </td>

                    {{-- Status --}}
                    <td style="padding: 12px; text-align: center;">
                        @if($u->is_active)
                        <span style="font-size: 9.5px; font-weight: 600; letter-spacing: 0.04em; padding: 2px 7px; background: rgba(74,122,92,0.14); color: var(--moss); text-transform: uppercase;">Active</span>
                        @else
                        <span style="font-size: 9.5px; font-weight: 600; letter-spacing: 0.04em; padding: 2px 7px; background: rgba(138,46,29,0.10); color: var(--burgundy); text-transform: uppercase;">Inactive</span>
                        @endif
                    </td>

                    {{-- Actions --}}
                    <td style="padding: 10px 12px; text-align: right; white-space: nowrap;">

                        {{-- Edit --}}
                        <button
                            type="button"
                            title="Edit user"
                            onclick="jhOpenEditUser({{ json_encode([
                                'id'             => $u->id,
                                'name'           => $u->name,
                                'email'          => $u->email,
                                'role'           => $u->role->value,
                                'hub_id'         => $u->hub_id,
                                'emp_id'         => $u->emp_id,
                                'designation'    => $u->designation,
                                'department'     => $u->department,
                                'contact_number' => $u->contact_number,
                                'is_active'      => $u->is_active,
                                'is_self'        => $u->id === $authUser->id,
                            ]) }})"
                            style="padding: 5px 9px; background: transparent; border: 1px solid var(--rule-2); cursor: pointer; color: var(--ink-3); font-family: inherit; font-size: 11px; transition: all 120ms; margin-right: 4px;"
                            onmouseenter="this.style.borderColor='var(--forest)';this.style.color='var(--forest)'"
                            onmouseleave="this.style.borderColor='var(--rule-2)';this.style.color='var(--ink-3)'"
                        ><x-lucide-pencil style="width: 11px; height: 11px;" /></button>

                        {{-- Toggle active / inactive --}}
                        @if($u->id !== $authUser->id)
                        <form method="POST" action="{{ route('users.toggle', $u) }}" style="display: inline;">
                            @csrf
                            <button
                                type="submit"
                                title="{{ $u->is_active ? 'Deactivate user' : 'Activate user' }}"
                                style="padding: 5px 9px; background: transparent; border: 1px solid var(--rule-2); cursor: pointer; font-family: inherit; font-size: 11px; transition: all 120ms; margin-right: 4px; color: {{ $u->is_active ? 'var(--ochre)' : 'var(--moss)' }};"
                                onmouseenter="this.style.borderColor=this.style.color"
                                onmouseleave="this.style.borderColor='var(--rule-2)'"
                            >
                                @if($u->is_active)
                                <x-lucide-user-x style="width: 11px; height: 11px;" />
                                @else
                                <x-lucide-user-check style="width: 11px; height: 11px;" />
                                @endif
                            </button>
                        </form>

                        {{-- Delete --}}
                        <form method="POST" action="{{ route('users.destroy', $u) }}" style="display: inline;" onsubmit="return confirm('Delete {{ addslashes($u->name) }}? This action cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button
                                type="submit"
                                title="Delete user permanently"
                                style="padding: 5px 9px; background: transparent; border: 1px solid var(--rule-2); cursor: pointer; color: var(--burgundy); font-family: inherit; font-size: 11px; transition: all 120ms;"
                                onmouseenter="this.style.borderColor='var(--burgundy)'"
                                onmouseleave="this.style.borderColor='var(--rule-2)'"
                            ><x-lucide-trash-2 style="width: 11px; height: 11px;" /></button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="padding: 40px; text-align: center; color: var(--ink-4); font-size: 13px;">
                        No users found. <button type="button" onclick="jhOpenModal('create-user')" style="background: none; border: none; color: var(--forest); cursor: pointer; font-family: inherit; font-size: 13px; text-decoration: underline;">Create the first user.</button>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Empty search state --}}
    <div id="jh-no-results" style="display: none; padding: 30px; text-align: center; color: var(--ink-4); font-size: 13px; border: 1px solid var(--rule); border-top: none; max-width: 1100px; background: var(--paper);">
        No users match your search. <button type="button" onclick="jhClearFilters()" style="background: none; border: none; color: var(--forest); cursor: pointer; font-family: inherit; font-size: 13px; text-decoration: underline;">Clear filters.</button>
    </div>

    {{-- Role legend --}}
    <div style="margin-top: 18px; max-width: 1100px;">
        <div class="label-cap" style="font-size: 9px; margin-bottom: 8px; color: var(--ink-4);">Role reference</div>
        <div style="display: flex; flex-wrap: wrap; gap: 8px;">
            @foreach($roles as $r)
            <div style="display: flex; align-items: center; gap: 6px; padding: 5px 10px; border: 1px solid var(--rule-2); background: var(--paper); font-size: 11px;">
                <span style="font-size: 9.5px; font-weight: 600; letter-spacing: 0.03em; padding: 2px 6px; {{ $roleStyle[$r->value] ?? '' }}">{{ $r->label() }}</span>
                <span style="color: var(--ink-3);">
                    {{ $r->isGlobalScope() === true ? 'All hubs' : ($r->isGlobalScope() === false ? 'Hub scoped' : 'Flexible scope') }}
                    · {{ $r->canWrite() ? 'Can write' : 'Read only' }}
                </span>
                <span style="color: var(--ink-4); font-size: 10px;">{{ $byRole->get($r->value, 0) }} {{ $byRole->get($r->value, 0) === 1 ? 'user' : 'users' }}</span>
            </div>
            @endforeach
        </div>
    </div>

</div>

{{-- ═══════════════════════════════════════════════════════════════
    CREATE USER MODAL
═══════════════════════════════════════════════════════════════ --}}
<x-jh-modal name="create-user" title="Add New User" maxWidth="680px">
    <form method="POST" action="{{ route('users.store') }}" style="display: flex; flex-direction: column; gap: 16px;">
        @csrf

        {{-- Row 1: Name + Email --}}
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
            <div>
                <label style="font-size: 11px; color: var(--ink-3); font-weight: 500; display: block; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.04em;">Full name <span style="color: var(--burgundy);">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Fatima Hussain" class="inp" style="width: 100%; font-size: 13px;" />
            </div>
            <div>
                <label style="font-size: 11px; color: var(--ink-3); font-weight: 500; display: block; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.04em;">Email address <span style="color: var(--burgundy);">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="user@justicehub.org" class="inp" style="width: 100%; font-size: 13px;" />
            </div>
        </div>

        {{-- Row 2: Password --}}
        <div>
            <label style="font-size: 11px; color: var(--ink-3); font-weight: 500; display: block; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.04em;">Password <span style="color: var(--burgundy);">*</span></label>
            <input type="password" name="password" required placeholder="Minimum 8 characters" class="inp" style="width: 100%; font-size: 13px;" />
        </div>

        {{-- Row 3: Role + Hub --}}
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; align-items: start;">
            <div>
                <label style="font-size: 11px; color: var(--ink-3); font-weight: 500; display: block; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.04em;">Role <span style="color: var(--burgundy);">*</span></label>
                <select name="role" id="create-role" required class="inp" style="width: 100%; font-size: 13px;" onchange="jhToggleHubField('create-role', 'create-hub-wrap')">
                    <option value="">— Select role —</option>
                    @foreach($roles as $r)
                    <option value="{{ $r->value }}" {{ old('role') === $r->value ? 'selected' : '' }}>{{ $r->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div id="create-hub-wrap">
                <label style="font-size: 11px; color: var(--ink-3); font-weight: 500; display: block; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.04em;">Hub assignment</label>
                <select name="hub_id" id="create-hub" class="inp" style="width: 100%; font-size: 13px;">
                    <option value="">— No hub (global) —</option>
                    @foreach($hubs as $hub)
                    <option value="{{ $hub->id }}" {{ old('hub_id') === $hub->id ? 'selected' : '' }}>{{ $hub->name }}</option>
                    @endforeach
                </select>
                <div id="create-hub-hint" style="font-size: 10.5px; color: var(--ink-4); margin-top: 4px;"></div>
            </div>
        </div>

        {{-- Row 4: EmpID + Designation --}}
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
            <div>
                <label style="font-size: 11px; color: var(--ink-3); font-weight: 500; display: block; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.04em;">Employee ID</label>
                <input type="text" name="emp_id" value="{{ old('emp_id') }}" placeholder="e.g. LAS-2024-001" class="inp mono" style="width: 100%; font-size: 12px;" />
            </div>
            <div>
                <label style="font-size: 11px; color: var(--ink-3); font-weight: 500; display: block; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.04em;">Designation</label>
                <input type="text" name="designation" value="{{ old('designation') }}" placeholder="e.g. Field Paralegal" class="inp" style="width: 100%; font-size: 13px;" />
            </div>
        </div>

        {{-- Row 5: Department + Contact --}}
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
            <div>
                <label style="font-size: 11px; color: var(--ink-3); font-weight: 500; display: block; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.04em;">Department</label>
                <input type="text" name="department" value="{{ old('department') }}" placeholder="e.g. Legal Aid" class="inp" style="width: 100%; font-size: 13px;" />
            </div>
            <div>
                <label style="font-size: 11px; color: var(--ink-3); font-weight: 500; display: block; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.04em;">Contact number</label>
                <input type="text" name="contact_number" value="{{ old('contact_number') }}" placeholder="e.g. +92-300-1234567" class="inp" style="width: 100%; font-size: 13px;" />
            </div>
        </div>

        {{-- Footer --}}
        <div style="display: flex; gap: 10px; justify-content: flex-end; padding-top: 8px; border-top: 1px solid var(--rule-2);">
            <button type="button" data-bs-dismiss="modal" style="padding: 8px 18px; background: transparent; border: 1px solid var(--rule); cursor: pointer; font-family: inherit; font-size: 13px; color: var(--ink-3);">Cancel</button>
            <button type="submit" class="btn-primary" style="padding: 8px 22px; font-size: 13px; display: flex; align-items: center; gap: 7px;">
                <x-lucide-user-plus style="width: 13px; height: 13px;" />
                Create User
            </button>
        </div>
    </form>
</x-jh-modal>

{{-- ═══════════════════════════════════════════════════════════════
    EDIT USER MODAL
═══════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modal-edit-user" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 680px; margin: 1.75rem auto;">
        <div class="modal-content" style="border: 1px solid var(--rule); border-radius: 4px; background: var(--paper); box-shadow: 0 12px 40px rgba(0,0,0,0.15); max-height: 92vh; overflow-y: auto;">
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 20px 24px 16px; border-bottom: 1px solid var(--rule-2);">
                <h3 id="edit-modal-title" class="serif" style="font-size: 20px; font-weight: 500; margin: 0; color: var(--forest);">Edit User</h3>
                <button type="button" data-bs-dismiss="modal" style="background: none; border: none; cursor: pointer; color: var(--ink-3); padding: 4px; line-height: 1;">
                    <x-lucide-x style="width: 16px; height: 16px;" />
                </button>
            </div>
            <div style="padding: 20px 24px 24px;">

                {{-- Self-edit warning (shown by JS) --}}
                <div id="edit-self-warning" style="display: none; padding: 9px 12px; background: rgba(195,145,60,0.10); border: 1px solid var(--ochre); color: var(--ochre); font-size: 12px; margin-bottom: 14px; display: flex; align-items: center; gap: 7px;">
                    <x-lucide-alert-triangle style="width: 13px; height: 13px; flex-shrink: 0;" />
                    You are editing your own account. Role cannot be changed.
                </div>

                <form id="edit-user-form" method="POST" action="" style="display: flex; flex-direction: column; gap: 16px;">
                    @csrf
                    @method('PATCH')

                    {{-- Row 1: Name + Email --}}
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div>
                            <label style="font-size: 11px; color: var(--ink-3); font-weight: 500; display: block; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.04em;">Full name <span style="color: var(--burgundy);">*</span></label>
                            <input type="text" id="edit-name" name="name" required class="inp" style="width: 100%; font-size: 13px;" />
                        </div>
                        <div>
                            <label style="font-size: 11px; color: var(--ink-3); font-weight: 500; display: block; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.04em;">Email address <span style="color: var(--burgundy);">*</span></label>
                            <input type="email" id="edit-email" name="email" required class="inp" style="width: 100%; font-size: 13px;" />
                        </div>
                    </div>

                    {{-- Row 2: New password --}}
                    <div>
                        <label style="font-size: 11px; color: var(--ink-3); font-weight: 500; display: block; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.04em;">New password <span style="font-size: 10px; font-weight: 400; text-transform: none; letter-spacing: 0;">(leave blank to keep current)</span></label>
                        <input type="password" id="edit-password" name="password" placeholder="Minimum 8 characters" class="inp" style="width: 100%; font-size: 13px;" />
                    </div>

                    {{-- Row 3: Role + Hub --}}
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; align-items: start;">
                        <div>
                            <label style="font-size: 11px; color: var(--ink-3); font-weight: 500; display: block; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.04em;">Role <span style="color: var(--burgundy);">*</span></label>
                            <select name="role" id="edit-role" required class="inp" style="width: 100%; font-size: 13px;" onchange="jhToggleHubField('edit-role', 'edit-hub-wrap')">
                                @foreach($roles as $r)
                                <option value="{{ $r->value }}">{{ $r->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="edit-hub-wrap">
                            <label style="font-size: 11px; color: var(--ink-3); font-weight: 500; display: block; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.04em;">Hub assignment</label>
                            <select name="hub_id" id="edit-hub" class="inp" style="width: 100%; font-size: 13px;">
                                <option value="">— No hub (global) —</option>
                                @foreach($hubs as $hub)
                                <option value="{{ $hub->id }}">{{ $hub->name }}</option>
                                @endforeach
                            </select>
                            <div id="edit-hub-hint" style="font-size: 10.5px; color: var(--ink-4); margin-top: 4px;"></div>
                        </div>
                    </div>

                    {{-- Row 4: EmpID + Designation --}}
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div>
                            <label style="font-size: 11px; color: var(--ink-3); font-weight: 500; display: block; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.04em;">Employee ID</label>
                            <input type="text" id="edit-emp-id" name="emp_id" class="inp mono" style="width: 100%; font-size: 12px;" />
                        </div>
                        <div>
                            <label style="font-size: 11px; color: var(--ink-3); font-weight: 500; display: block; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.04em;">Designation</label>
                            <input type="text" id="edit-designation" name="designation" class="inp" style="width: 100%; font-size: 13px;" />
                        </div>
                    </div>

                    {{-- Row 5: Department + Contact --}}
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div>
                            <label style="font-size: 11px; color: var(--ink-3); font-weight: 500; display: block; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.04em;">Department</label>
                            <input type="text" id="edit-department" name="department" class="inp" style="width: 100%; font-size: 13px;" />
                        </div>
                        <div>
                            <label style="font-size: 11px; color: var(--ink-3); font-weight: 500; display: block; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.04em;">Contact number</label>
                            <input type="text" id="edit-contact" name="contact_number" class="inp" style="width: 100%; font-size: 13px;" />
                        </div>
                    </div>

                    {{-- Row 6: Active toggle --}}
                    <div style="padding: 12px 14px; background: var(--parchment); border: 1px solid var(--rule-2); display: flex; align-items: center; gap: 12px;">
                        <input type="hidden" name="is_active" value="0" />
                        <input type="checkbox" id="edit-active" name="is_active" value="1" style="width: 15px; height: 15px; accent-color: var(--forest); cursor: pointer;" />
                        <div>
                            <label for="edit-active" style="font-size: 13px; color: var(--ink); font-weight: 500; cursor: pointer;">Account active</label>
                            <div style="font-size: 11px; color: var(--ink-4); margin-top: 2px;">Inactive users cannot log in. Their data is preserved.</div>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div style="display: flex; gap: 10px; justify-content: flex-end; padding-top: 8px; border-top: 1px solid var(--rule-2);">
                        <button type="button" data-bs-dismiss="modal" style="padding: 8px 18px; background: transparent; border: 1px solid var(--rule); cursor: pointer; font-family: inherit; font-size: 13px; color: var(--ink-3);">Cancel</button>
                        <button type="submit" class="btn-primary" style="padding: 8px 22px; font-size: 13px; display: flex; align-items: center; gap: 7px;">
                            <x-lucide-save style="width: 13px; height: 13px;" />
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
    JavaScript
═══════════════════════════════════════════════════════════════ --}}
<script>
// Roles that require / optionally require a hub
const JH_HUB_REQUIRED = ['hub-admin', 'data-entry', 'complaint-investigator'];
const JH_HUB_OPTIONAL  = ['viewer'];
const JH_NO_HUB        = ['head', 'me-lead'];

// Show / hide hub field based on selected role
function jhToggleHubField(roleSelectId, hubWrapId) {
    const role    = document.getElementById(roleSelectId).value;
    const wrap    = document.getElementById(hubWrapId);
    const hubSel  = wrap.querySelector('select');
    const hint    = wrap.querySelector('[id$="-hub-hint"]');
    const noHubOpt = hubSel.querySelector('option[value=""]');

    if (JH_NO_HUB.includes(role)) {
        // Global roles: dim field, no hub needed, show "No hub" option
        wrap.style.opacity = '0.45';
        wrap.style.pointerEvents = 'none';
        hubSel.required = false;
        hubSel.value = '';
        if (noHubOpt) { noHubOpt.style.display = ''; noHubOpt.textContent = '— No hub (global) —'; }
        if (hint) {
            hint.textContent = 'Global role — sees all hubs automatically.';
            hint.style.color = 'var(--ink-4)';
        }
    } else if (JH_HUB_REQUIRED.includes(role)) {
        // Hub-scoped roles: hub is mandatory, hide the "no hub" placeholder
        wrap.style.opacity = '1';
        wrap.style.pointerEvents = '';
        hubSel.required = true;
        // Force a real hub to be selected — remove the "no hub" blank option
        if (noHubOpt) noHubOpt.style.display = 'none';
        if (!hubSel.value) hubSel.selectedIndex = 1; // default to first real hub
        if (hint) {
            hint.innerHTML = '⚠ Select the hub this person manages.';
            hint.style.color = 'var(--ochre)';
        }
    } else {
        // Viewer — hub is optional (restricts scope if set)
        wrap.style.opacity = '1';
        wrap.style.pointerEvents = '';
        hubSel.required = false;
        if (noHubOpt) { noHubOpt.style.display = ''; noHubOpt.textContent = '— No hub (all hubs) —'; }
        if (hint) {
            hint.textContent = 'Optional — leave blank to allow viewing all hubs.';
            hint.style.color = 'var(--ink-4)';
        }
    }
}

// Open edit modal and pre-populate fields
function jhOpenEditUser(u) {
    const form = document.getElementById('edit-user-form');
    form.action = `/settings/users/${u.id}`;

    document.getElementById('edit-modal-title').textContent = `Edit — ${u.name}`;
    document.getElementById('edit-name').value        = u.name  || '';
    document.getElementById('edit-email').value       = u.email || '';
    document.getElementById('edit-password').value    = '';
    document.getElementById('edit-role').value        = u.role  || '';
    document.getElementById('edit-hub').value         = u.hub_id || '';
    document.getElementById('edit-emp-id').value      = u.emp_id || '';
    document.getElementById('edit-designation').value = u.designation || '';
    document.getElementById('edit-department').value  = u.department  || '';
    document.getElementById('edit-contact').value     = u.contact_number || '';
    document.getElementById('edit-active').checked   = !!u.is_active;

    // Self-edit: show warning, disable role select
    const warning  = document.getElementById('edit-self-warning');
    const roleSelect = document.getElementById('edit-role');
    if (u.is_self) {
        warning.style.display = 'flex';
        roleSelect.disabled   = true;
    } else {
        warning.style.display = 'none';
        roleSelect.disabled   = false;
    }

    // Update hub field visibility
    jhToggleHubField('edit-role', 'edit-hub-wrap');

    new bootstrap.Modal(document.getElementById('modal-edit-user')).show();
}

// Re-enable role select before form submit (disabled fields are not submitted)
document.getElementById('edit-user-form').addEventListener('submit', function () {
    document.getElementById('edit-role').disabled = false;
});

// Client-side search + filter
function jhFilterUsers() {
    const term       = document.getElementById('jh-user-search').value.toLowerCase();
    const roleFilter = document.getElementById('jh-role-filter').value;
    const hubFilter  = document.getElementById('jh-hub-filter').value;
    const statFilter = document.getElementById('jh-status-filter').value;

    const rows = document.querySelectorAll('[data-user-row]');
    let visible = 0;

    rows.forEach(function(row) {
        const name   = row.dataset.userName  || '';
        const email  = row.dataset.userEmail || '';
        const role   = row.dataset.userRole  || '';
        const hub    = row.dataset.userHub   || 'none';
        const active = row.dataset.userActive;

        const matchSearch = !term || name.includes(term) || email.includes(term);
        const matchRole   = !roleFilter || role === roleFilter;
        const matchHub    = !hubFilter  || hub  === hubFilter;
        const matchStat   = !statFilter || active === statFilter;

        if (matchSearch && matchRole && matchHub && matchStat) {
            row.style.display = '';
            visible++;
        } else {
            row.style.display = 'none';
        }
    });

    document.getElementById('jh-visible-count').textContent = visible;
    document.getElementById('jh-no-results').style.display  = (visible === 0) ? 'block' : 'none';
}

// Clear all filters
function jhClearFilters() {
    document.getElementById('jh-user-search').value  = '';
    document.getElementById('jh-role-filter').value  = '';
    document.getElementById('jh-hub-filter').value   = '';
    document.getElementById('jh-status-filter').value = '';
    jhFilterUsers();
}

// Auto-open create modal if there are validation errors (from failed store)
@if($errors->any() && !session('success'))
document.addEventListener('DOMContentLoaded', function () {
    jhOpenModal('create-user');
});
@endif
</script>

</x-layouts.app>
