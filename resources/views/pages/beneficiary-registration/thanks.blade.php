@extends('layouts.public')

@section('title', __('beneficiary_registration.thanks_title'))

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-body p-4 p-md-5 text-center">
        <div class="mb-3">
            <i class="ti ti-circle-check text-success" style="font-size: 3rem;"></i>
        </div>
        <h1 class="h3 mb-2">{{ __('beneficiary_registration.thanks_title') }}</h1>
        <p class="text-muted mb-4">{{ __('beneficiary_registration.thanks_body') }}</p>
        <div class="alert alert-light border d-inline-block px-4 py-3 mb-4">
            <div class="small text-muted mb-1">{{ __('beneficiary_registration.reference') }}</div>
            <div class="fw-bold font-monospace fs-5">{{ $application->uuid }}</div>
        </div>
        <div>
            <a href="{{ route('register') }}" class="btn btn-outline-primary">{{ __('beneficiary_registration.title') }}</a>
        </div>
    </div>
</div>
@endsection
