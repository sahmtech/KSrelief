@extends('layouts.admin')

@section('title', __('pages.roles.title'))

@section('content')
<x-page-header
    :title="__('pages.roles.title')"
    :subtitle="__('pages.roles.subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.administration')],
        ['label' => __('pages.roles.title')],
    ]"
>
    <x-slot:actions>
        @can('role.create')
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createRoleModal">
                <i class="ti ti-plus me-1"></i> {{ __('pages.roles.add') }}
            </button>
        @endcan
    </x-slot:actions>
</x-page-header>

@if($errors->any() && old('_modal'))
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <div class="d-flex align-items-start gap-2">
            <i class="ti ti-alert-circle mt-1"></i>
            <div>
                <strong>{{ __('pages.roles.validation.fix_errors') }}</strong>
                <ul class="mb-0 mt-1 small">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('common.close') }}"></button>
    </div>
@endif

<x-card :title="__('pages.roles.title')" :subtitle="__('pages.roles.subtitle')">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>{{ __('pages.roles.table.name') }}</th>
                    <th>{{ __('pages.roles.table.slug') }}</th>
                    <th>{{ __('pages.roles.table.permissions') }}</th>
                    <th class="text-end">{{ __('pages.roles.table.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($roles as $role)
                    @php
                        $isSystem = in_array($role->name, \App\Enums\SystemRole::values(), true);
                        $rolePayload = [
                            'id' => $role->id,
                            'name' => $role->name,
                            'label' => \App\Enums\SystemRole::tryFrom($role->name)?->label() ?? $role->name,
                            'is_system' => $isSystem,
                            'permissions' => $role->permissions->pluck('name')->values()->all(),
                        ];
                    @endphp
                    <tr>
                        <td class="fw-medium">
                            {{ $rolePayload['label'] }}
                            @if($isSystem)
                                <span class="badge bg-primary-subtle text-primary border ms-1" style="font-size: 0.6875rem;">{{ __('pages.roles.system_role') }}</span>
                            @endif
                        </td>
                        <td><code>{{ $role->name }}</code></td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                {{ __('pages.roles.permissions_count', ['count' => $role->permissions->count()]) }}
                            </span>
                        </td>
                        <td class="text-end">
                            @can('role.update')
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary btn-edit-role"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editRoleModal"
                                    data-role="{{ json_encode($rolePayload) }}"
                                >
                                    <i class="ti ti-pencil me-1"></i> {{ __('pages.roles.edit') }}
                                </button>
                            @endcan

                            @can('role.delete')
                                @if(! $isSystem)
                                    <form method="POST" action="{{ route('administration.roles.destroy', $role) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-outline-danger"
                                            data-confirm="{{ __('pages.roles.confirm_delete') }}"
                                        >
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-card>

@can('role.create')
    <x-modal id="createRoleModal" :title="__('pages.roles.create_modal_title')" size="xl">
        <form method="POST" action="{{ route('administration.roles.store') }}" id="createRoleForm">
            @csrf
            <input type="hidden" name="_modal" value="create">

            @if($errors->any() && old('_modal') === 'create')
                <div class="alert alert-danger py-2 px-3 mb-3" style="font-size: 0.875rem;">
                    <i class="ti ti-alert-circle me-1"></i> {{ __('pages.roles.validation.fix_errors') }}
                </div>
            @endif

            <x-form-input
                :label="__('pages.roles.name')"
                name="name"
                :value="old('name')"
                :placeholder="__('pages.roles.name_placeholder')"
                :hint="__('pages.roles.name_hint')"
                required
            />
            <label class="form-group-admin__label mb-2">{{ __('pages.roles.permissions') }}</label>
            @include('pages.administration.partials.role-permissions', [
                'permissionGroups' => $permissionGroups,
                'selected' => old('_modal') === 'create' ? old('permissions', []) : [],
                'inputIdPrefix' => 'create',
            ])
        </form>
        <x-slot:footer>
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
            <button type="submit" form="createRoleForm" class="btn btn-primary">{{ __('common.create') }}</button>
        </x-slot:footer>
    </x-modal>
@endcan

@can('role.update')
    <x-modal id="editRoleModal" :title="__('pages.roles.edit_modal_title')" size="xl">
        <form method="POST" action="" id="editRoleForm">
            @csrf
            @method('PUT')
            <input type="hidden" name="_modal" value="edit">
            <input type="hidden" name="_role_id" id="edit_role_id" value="{{ old('_role_id') }}">
            <input type="hidden" name="_is_system" id="edit_is_system" value="{{ old('_is_system', '0') }}">

            @if($errors->any() && old('_modal') === 'edit')
                <div class="alert alert-danger py-2 px-3 mb-3" style="font-size: 0.875rem;">
                    <i class="ti ti-alert-circle me-1"></i> {{ __('pages.roles.validation.fix_errors') }}
                </div>
            @endif

            <div id="editRoleNameField">
                <x-form-input
                    :label="__('pages.roles.name')"
                    name="name"
                    id="edit_role_name"
                    :value="old('name')"
                    :hint="__('pages.roles.name_hint')"
                    required
                />
            </div>
            <div id="editRoleSystemBadge" class="alert alert-light border py-2 px-3 mb-3 d-none" style="font-size: 0.875rem;">
                <i class="ti ti-shield me-1"></i>
                <strong>{{ __('pages.roles.system_role') }}:</strong>
                <span id="editRoleSystemLabel"></span> — <code id="editRoleSystemSlug"></code>
            </div>
            <label class="form-group-admin__label mb-2">{{ __('pages.roles.permissions') }}</label>
            <div id="editRolePermissions">
                @include('pages.administration.partials.role-permissions', [
                    'permissionGroups' => $permissionGroups,
                    'selected' => old('_modal') === 'edit' ? old('permissions', []) : [],
                    'inputIdPrefix' => 'edit',
                ])
            </div>
        </form>
        <x-slot:footer>
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
            <button type="submit" form="editRoleForm" class="btn btn-primary">{{ __('common.save_changes') }}</button>
        </x-slot:footer>
    </x-modal>
@endcan
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const baseUrl = @json(url('administration/roles'));

    function populateEditForm(role) {
        const form = document.getElementById('editRoleForm');
        const nameField = document.getElementById('editRoleNameField');
        const nameInput = document.getElementById('edit_role_name');
        const systemBadge = document.getElementById('editRoleSystemBadge');
        const systemLabel = document.getElementById('editRoleSystemLabel');
        const systemSlug = document.getElementById('editRoleSystemSlug');
        const roleIdInput = document.getElementById('edit_role_id');
        const isSystemInput = document.getElementById('edit_is_system');

        if (!form || !role) return;

        form.action = `${baseUrl}/${role.id}`;
        roleIdInput.value = role.id;
        isSystemInput.value = role.is_system ? '1' : '0';

        if (role.is_system) {
            nameField.classList.add('d-none');
            systemBadge.classList.remove('d-none');
            systemLabel.textContent = role.label;
            systemSlug.textContent = role.name;
            if (nameInput) {
                nameInput.removeAttribute('required');
                nameInput.disabled = true;
            }
        } else {
            nameField.classList.remove('d-none');
            systemBadge.classList.add('d-none');
            if (nameInput) {
                nameInput.disabled = false;
                nameInput.setAttribute('required', 'required');
                if (role.name) {
                    nameInput.value = role.name;
                }
            }
        }

        const permissions = role.permissions || [];
        document.querySelectorAll('#editRolePermissions input[type="checkbox"]').forEach((checkbox) => {
            checkbox.checked = permissions.includes(checkbox.value);
        });
    }

    const editModal = document.getElementById('editRoleModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', (event) => {
            const button = event.relatedTarget;
            if (!button?.dataset.role) return;
            populateEditForm(JSON.parse(button.dataset.role));
        });
    }

    @if($reopenCreateModal)
        const createModal = document.getElementById('createRoleModal');
        if (createModal) {
            bootstrap.Modal.getOrCreateInstance(createModal).show();
        }
    @endif

    @if($editRolePayload)
        populateEditForm(@json($editRolePayload));
        if (editModal) {
            bootstrap.Modal.getOrCreateInstance(editModal).show();
        }
    @endif
});
</script>
@endpush
