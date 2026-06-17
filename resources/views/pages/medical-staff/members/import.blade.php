@extends('layouts.admin')

@section('title', __('members.import.title'))

@section('content')
<x-page-header
    :title="__('members.import.title')"
    :subtitle="__('members.import.subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.medical_staff')],
        ['label' => __('members.title'), 'url' => route('medical-staff.members.index')],
        ['label' => __('members.import.title')],
    ]"
>
    <x-slot:actions>
        <a href="{{ route('medical-staff.members.import.template') }}" class="btn btn-outline-primary btn-sm">
            <i class="ti ti-download me-1"></i> {{ __('members.import.download_template') }}
        </a>
    </x-slot:actions>
</x-page-header>

<div class="row g-3">
    <div class="col-lg-5">
        <x-card :title="__('members.import.upload_title')">
            <p class="text-muted mb-3" style="font-size: 0.875rem;">{{ __('members.import.upload_hint') }}</p>

            <form method="POST" action="{{ route('medical-staff.members.import.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-group-admin__label" for="import_file">Excel / CSV</label>
                    <input
                        type="file"
                        name="file"
                        id="import_file"
                        class="form-group-admin__input @error('file') is-invalid @enderror"
                        accept=".xlsx,.xls,.csv"
                        required
                    >
                    @error('file')
                        <div class="form-group-admin__error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-upload me-1"></i> {{ __('members.import.submit') }}
                    </button>
                    <a href="{{ route('medical-staff.members.index') }}" class="btn btn-outline-secondary">{{ __('common.cancel') }}</a>
                </div>
            </form>
        </x-card>

        <x-card :title="__('members.import.instructions_title')" class="mt-3">
            <ul class="mb-0 ps-3" style="font-size: 0.875rem;">
                @foreach(__('members.import.instructions') as $instruction)
                    <li class="mb-2 text-muted">{{ $instruction }}</li>
                @endforeach
            </ul>
        </x-card>
    </div>

    <div class="col-lg-7">
        <x-card :title="__('members.import.reference_roles')" :flush="true" class="mb-3">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('members.fields.role') }}</th>
                            <th>Code</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($memberRoles as $role)
                            <tr>
                                <td>{{ $role->name }}</td>
                                <td><code>{{ $role->code }}</code></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>

        @if($specialties->isNotEmpty())
            <x-card :title="__('members.import.reference_specialties')" :flush="true">
                <div class="d-flex flex-wrap gap-2 p-3">
                    @foreach($specialties as $specialty)
                        <span class="badge bg-light text-dark border">{{ $specialty->name }}</span>
                    @endforeach
                </div>
            </x-card>
        @endif
    </div>
</div>

@if($importResult)
    <x-card :title="__('members.import.result_title')" class="mt-4" :flush="true">
        <div class="row g-3 p-3 border-bottom">
            <div class="col-sm-4">
                <div class="text-success fw-semibold">{{ __('members.import.result_success') }}</div>
                <div class="fs-4">{{ $importResult->success }}</div>
            </div>
            <div class="col-sm-4">
                <div class="text-danger fw-semibold">{{ __('members.import.result_failed') }}</div>
                <div class="fs-4">{{ $importResult->failed }}</div>
            </div>
            <div class="col-sm-4">
                <div class="text-warning fw-semibold">{{ __('members.import.result_skipped') }}</div>
                <div class="fs-4">{{ $importResult->skipped }}</div>
            </div>
        </div>

        @if(count($importResult->errors) > 0)
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th style="width: 80px;">{{ __('members.import.result_row') }}</th>
                            <th>{{ __('members.import.result_message') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($importResult->errors as $error)
                            <tr>
                                <td>{{ $error['row'] ?: '—' }}</td>
                                <td class="text-danger">{{ $error['message'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-card>
@endif
@endsection
