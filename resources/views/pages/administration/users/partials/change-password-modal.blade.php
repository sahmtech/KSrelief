@props(['user'])

@can('changePassword', $user)
    <x-modal id="changePasswordModal" :title="__('users.change_password.title')" size="lg">
        <p class="text-muted mb-3" style="font-size: 0.875rem;">{{ __('users.change_password.subtitle') }}</p>
        <form method="POST" action="{{ route('administration.users.password.update', $user) }}" id="changePasswordForm">
            @csrf
            @method('PUT')
            <x-form-input
                :label="__('users.fields.password')"
                name="password"
                type="password"
                autocomplete="new-password"
                :hint="__('users.validation.password_hint')"
                :revealable="true"
                required
            />
            <x-form-input
                :label="__('users.fields.password_confirmation')"
                name="password_confirmation"
                type="password"
                autocomplete="new-password"
                :revealable="true"
                required
            />
        </form>
        <x-slot:footer>
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
            <button type="submit" form="changePasswordForm" class="btn btn-primary">{{ __('users.change_password.submit') }}</button>
        </x-slot:footer>
    </x-modal>
@endcan
