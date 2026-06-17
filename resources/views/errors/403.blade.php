@extends('layouts.admin')

@section('title', __('Forbidden'))

@section('content')
<div class="text-center py-5">
    <div class="empty-state__icon mx-auto mb-3" style="width: 72px; height: 72px;">
        <i class="ti ti-lock"></i>
    </div>
    <h1 class="h3 mb-2">403 — {{ __('Forbidden') }}</h1>
    <p class="text-muted mb-4">{{ $exception?->getMessage() ?: __('You do not have permission to access this resource.') }}</p>
    <a href="{{ route('dashboard') }}" class="btn btn-primary">{{ __('menu.dashboard') }}</a>
</div>
@endsection
