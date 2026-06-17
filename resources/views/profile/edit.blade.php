<x-layouts.app>
@php $user = auth()->user(); @endphp

<div style="padding: 28px 36px 60px; max-width: 720px; margin: 0 auto;">

    {{-- Header --}}
    <div style="margin-bottom: 28px; padding-bottom: 18px; border-bottom: 1px solid var(--rule);">
        <div class="label-cap" style="font-size: 9.5px; margin-bottom: 6px;">Account</div>
        <h1 class="serif" style="font-size: 32px; font-weight: 400; margin: 0;">My Profile</h1>
        <div style="font-size: 13px; color: var(--ink-3); margin-top: 6px;">
            {{ $user->designation ?: $user->role->label() }} · {{ $user->hub_id ?: 'All Hubs' }}
        </div>
    </div>

    @if(session('status') === 'profile-updated')
    <div style="padding: 10px 16px; background: var(--moss-tint); border: 1px solid rgba(74,122,92,0.2); color: var(--moss); font-size: 12.5px; font-weight: 500; margin-bottom: 18px;">
        Profile updated successfully.
    </div>
    @endif

    @if(session('status') === 'password-updated')
    <div style="padding: 10px 16px; background: var(--moss-tint); border: 1px solid rgba(74,122,92,0.2); color: var(--moss); font-size: 12.5px; font-weight: 500; margin-bottom: 18px;">
        Password changed successfully.
    </div>
    @endif

    {{-- ═══ Profile Info ═══ --}}
    <div class="card" style="padding: 24px; margin-bottom: 18px;">
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 18px;">
            <x-lucide-user style="width:15px;height:15px;color:var(--forest);" />
            <div class="label-cap" style="font-size: 10px;">Profile Information</div>
        </div>

        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('patch')

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                <div>
                    <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:6px;">Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                           style="width:100%; padding:10px 12px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink); font-size:13px; font-family:inherit; box-sizing:border-box; border-radius:2px;">
                    @error('name') <div style="font-size:11px; color:var(--burgundy); margin-top:4px;">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:6px;">Email</label>
                    <input type="email" value="{{ $user->email }}" disabled
                           style="width:100%; padding:10px 12px; border:1px solid var(--rule); background:var(--paper); color:var(--ink-3); font-size:13px; font-family:inherit; box-sizing:border-box; border-radius:2px; cursor:not-allowed;">
                    <div style="font-size:10.5px; color:var(--ink-4); margin-top:4px;">Email can only be changed by an administrator.</div>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end;">
                <button type="submit" class="btn-primary" style="font-size: 12px; padding: 8px 18px;">
                    <x-lucide-check style="width:12px;height:12px;" /> Save changes
                </button>
            </div>
        </form>
    </div>

    {{-- ═══ Change Password ═══ --}}
    <div class="card" style="padding: 24px; margin-bottom: 18px;">
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
            <x-lucide-lock style="width:15px;height:15px;color:var(--ochre);" />
            <div class="label-cap" style="font-size: 10px;">Change Password</div>
        </div>
        <div style="font-size: 12px; color: var(--ink-3); margin-bottom: 18px;">Use a strong password with at least 8 characters.</div>

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            @method('put')

            <div style="margin-bottom: 14px;">
                <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:6px;">
                    Current Password <span style="color:var(--burgundy);">*</span>
                </label>
                <input type="password" name="current_password" required autocomplete="current-password"
                       style="width:100%; padding:10px 12px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink); font-size:13px; font-family:inherit; box-sizing:border-box; border-radius:2px;">
                @error('current_password', 'updatePassword') <div style="font-size:11px; color:var(--burgundy); margin-top:4px;">{{ $message }}</div> @enderror
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px;">
                <div>
                    <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:6px;">
                        New Password <span style="color:var(--burgundy);">*</span>
                    </label>
                    <input type="password" name="password" required autocomplete="new-password"
                           style="width:100%; padding:10px 12px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink); font-size:13px; font-family:inherit; box-sizing:border-box; border-radius:2px;">
                    @error('password', 'updatePassword') <div style="font-size:11px; color:var(--burgundy); margin-top:4px;">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:6px;">
                        Confirm New Password <span style="color:var(--burgundy);">*</span>
                    </label>
                    <input type="password" name="password_confirmation" required autocomplete="new-password"
                           style="width:100%; padding:10px 12px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink); font-size:13px; font-family:inherit; box-sizing:border-box; border-radius:2px;">
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end;">
                <button type="submit" class="btn-primary" style="font-size: 12px; padding: 8px 18px; background: var(--ochre); border-color: var(--ochre);">
                    <x-lucide-lock style="width:12px;height:12px;" /> Change password
                </button>
            </div>
        </form>
    </div>

    {{-- ═══ Account Info (read-only) ═══ --}}
    <div class="card" style="padding: 24px;">
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 16px;">
            <x-lucide-info style="width:15px;height:15px;color:var(--ink-3);" />
            <div class="label-cap" style="font-size: 10px;">Account Details</div>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
            <div>
                <div style="font-size:10px; color:var(--ink-4); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:3px;">Role</div>
                <div style="font-size:13px; font-weight:500; color:var(--ink);">{{ $user->designation ?: $user->role->label() }}</div>
            </div>
            <div>
                <div style="font-size:10px; color:var(--ink-4); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:3px;">Hub</div>
                <div style="font-size:13px; font-weight:500; color:var(--ink);">{{ $user->hub?->name ?? 'All Hubs (Global)' }}</div>
            </div>
            <div>
                <div style="font-size:10px; color:var(--ink-4); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:3px;">Member since</div>
                <div style="font-size:13px; color:var(--ink-2);">{{ $user->created_at->format('M d, Y') }}</div>
            </div>
            <div>
                <div style="font-size:10px; color:var(--ink-4); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:3px;">Status</div>
                <div style="font-size:13px; font-weight:500; color:var(--moss);">Active</div>
            </div>
        </div>
        <div style="margin-top: 14px; font-size: 11.5px; color: var(--ink-4); font-style: italic;">
            Role and hub assignment can only be changed by an administrator.
        </div>
    </div>

</div>
</x-layouts.app>
