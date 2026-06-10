<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Delete Account') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <button onclick="jhOpenModal('confirm-user-deletion')" class="btn-primary" style="background: var(--burgundy); border-color: var(--burgundy);">
        {{ __('Delete Account') }}
    </button>

    <x-jh-modal name="confirm-user-deletion" title="Delete Account" maxWidth="480px">
        <form method="post" action="{{ route('profile.destroy') }}">
            @csrf
            @method('delete')

            <p style="font-size: 13px; color: var(--ink-3); margin: 0 0 16px 0; line-height: 1.6;">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
            </p>

            <div style="margin-bottom: 16px;">
                <label for="delete-password" style="display: block; margin-bottom: 6px; font-size: 10px; font-weight: 500; letter-spacing: 0.06em; text-transform: uppercase; color: var(--ink-3);">Password</label>
                <input id="delete-password" name="password" type="password" class="inp" style="width: 100%;" placeholder="Enter your password" />
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; padding-top: 12px; border-top: 1px solid var(--rule-2);">
                <button type="button" data-bs-dismiss="modal" style="padding: 8px 18px; background: transparent; border: 1px solid var(--rule); cursor: pointer; font-family: inherit; font-size: 13px; color: var(--ink-3);">
                    {{ __('Cancel') }}
                </button>
                <button type="submit" class="btn-primary" style="background: var(--burgundy); border-color: var(--burgundy);">
                    {{ __('Delete Account') }}
                </button>
            </div>
        </form>
    </x-jh-modal>

    @if($errors->userDeletion->isNotEmpty())
    <script>document.addEventListener('DOMContentLoaded', function(){ jhOpenModal('confirm-user-deletion'); });</script>
    @endif
</section>
