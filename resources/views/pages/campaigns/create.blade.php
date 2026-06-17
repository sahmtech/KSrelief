@extends('layouts.admin')

@section('title', __('campaigns.create_title'))

@section('content')
<x-page-header
    :title="__('campaigns.create_title')"
    :subtitle="__('campaigns.create_subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.campaign_management')],
        ['label' => __('campaigns.title'), 'url' => route('campaigns.index')],
        ['label' => __('campaigns.create_title')],
    ]"
/>

<form method="POST" action="{{ route('campaigns.store') }}">
    @csrf

    @include('pages.campaigns.partials.form', [
        'statuses' => $statuses,
    ])

    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i> {{ __('common.create') }}</button>
        <a href="{{ route('campaigns.index') }}" class="btn btn-outline-secondary">{{ __('common.cancel') }}</a>
    </div>
</form>
@endsection
