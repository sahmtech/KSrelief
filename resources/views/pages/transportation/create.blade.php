@extends('layouts.admin')

@section('title', __('transportation.create_title'))

@section('content')
<x-page-header
    :title="__('transportation.create_title')"
    :subtitle="__('transportation.create_subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.operations')],
        ['label' => __('transportation.title'), 'url' => route('operations.transportation.index')],
        ['label' => __('transportation.create_title')],
    ]"
/>

<x-card>
    <form method="POST" action="{{ route('operations.transportation.store') }}">
        @csrf
        @include('pages.transportation.partials.form', [
            'campaigns' => $campaigns,
            'locations' => $locations,
            'tripTypes' => $tripTypes,
            'selectedCampaignId' => $selectedCampaignId,
            'formAction' => route('operations.transportation.create'),
        ])
        <div class="d-flex gap-2 mt-3">
            <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>{{ __('common.save') }}</button>
            <a href="{{ route('operations.transportation.index') }}" class="btn btn-light">{{ __('common.cancel') }}</a>
        </div>
    </form>
</x-card>
@endsection
