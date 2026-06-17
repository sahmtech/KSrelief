@props([
    'permissionGroups',
    'selected' => [],
    'inputIdPrefix' => 'perm',
])

<div class="role-permissions">
    @foreach($permissionGroups as $group => $permissions)
        <div class="mb-3">
            <div class="fw-semibold text-muted mb-2" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.04em;">
                {{ __('permissions.groups.' . $group) }}
            </div>
            <div class="row g-2">
                @foreach($permissions as $permission)
                    <div class="col-md-6 col-lg-4">
                        <div class="form-check">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="permissions[]"
                                value="{{ $permission }}"
                                id="{{ $inputIdPrefix }}_{{ str_replace('.', '_', $permission) }}"
                                {{ in_array($permission, $selected, true) ? 'checked' : '' }}
                            >
                            <label class="form-check-label" for="{{ $inputIdPrefix }}_{{ str_replace('.', '_', $permission) }}" style="font-size: 0.8125rem;">
                                {{ __('permissions.names.' . str_replace('.', '_', $permission)) }}
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
