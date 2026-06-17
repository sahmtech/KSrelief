@props([
    'roles',
    'selected' => [],
    'inputIdPrefix' => 'role',
])

<div class="role-checkboxes">
    <p class="text-muted mb-3" style="font-size: 0.8125rem;">{{ __('users.messages.select_roles') }}</p>
    <div class="row g-3">
        @foreach($roles as $role)
            @php
                $roleLabel = \App\Enums\SystemRole::tryFrom($role->name)?->label() ?? $role->name;
                $inputId = $inputIdPrefix . '_' . $role->name;
            @endphp
            <div class="col-sm-6 col-md-4 col-xl-3">
                <div class="role-checkboxes__item border rounded p-3 h-100 bg-light">
                    <div class="form-check mb-0">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="roles[]"
                            value="{{ $role->name }}"
                            id="{{ $inputId }}"
                            {{ in_array($role->name, $selected, true) ? 'checked' : '' }}
                        >
                        <label class="form-check-label w-100" for="{{ $inputId }}">
                            <span class="d-block fw-medium" style="font-size: 0.875rem;">{{ $roleLabel }}</span>
                            <span class="text-muted d-block mt-1" style="font-size: 0.75rem;"><code>{{ $role->name }}</code></span>
                        </label>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    @error('roles')
        <div class="form-group-admin__error mt-2">{{ $message }}</div>
    @enderror
    @error('roles.*')
        <div class="form-group-admin__error mt-2">{{ $message }}</div>
    @enderror
</div>
