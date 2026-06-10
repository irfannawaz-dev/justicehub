<x-guest-layout>

    {{-- Heading --}}
    <div style="margin-bottom: 32px;">
        <div class="auth-form-title">Welcome back</div>
        <div class="auth-form-sub">Sign in to your Justice Hub account to continue.</div>
    </div>

    @if (session('status'))
        <div class="auth-alert-success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}" style="display:flex;flex-direction:column;gap:20px;">
        @csrf

        {{-- Email --}}
        <div>
            <label for="email" class="auth-label">Email address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                autocomplete="username" class="inp" style="width:100%;" placeholder="you@lasorg.pk">
            @error('email')<div class="auth-error">{{ $message }}</div>@enderror
        </div>

        {{-- Password --}}
        <div>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:7px;">
                <label for="password" class="auth-label" style="margin-bottom:0;">Password</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="auth-link">Forgot password?</a>
                @endif
            </div>
            <div data-pw-wrapper style="position:relative;">
                <input id="password" type="password" name="password" required autocomplete="current-password"
                    class="inp" style="width:100%; padding-right:40px;" placeholder="••••••••">
                <button type="button" onclick="jhTogglePassword(this)" title="Show password"
                    style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;padding:0;cursor:pointer;color:var(--ink-4);display:flex;align-items:center;">
                    <span class="icon-eye"><x-lucide-eye style="width:16px;height:16px;" /></span>
                    <span class="icon-eye-off" style="display:none;"><x-lucide-eye-off style="width:16px;height:16px;" /></span>
                </button>
            </div>
            @error('password')<div class="auth-error">{{ $message }}</div>@enderror
        </div>

        {{-- Remember me --}}
        <label class="auth-remember">
            <input type="checkbox" name="remember" id="remember_me">
            <span>Keep me signed in for 30 days</span>
        </label>

        {{-- Submit --}}
        <div>
            <div class="auth-field-divider"></div>
            <button type="submit" class="btn-primary auth-submit">Sign In to Justice Hub</button>
        </div>
    </form>

</x-guest-layout>
