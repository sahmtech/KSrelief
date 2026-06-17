@extends('layouts.admin')

@section('title', __('transportation.edit_title'))

@section('content')
<x-page-header
    :title="__('transportation.edit_title')"
    :subtitle="$trip->trip_code"
    :breadcrumbs="[
        ['label' => __('menu.operations')],
        ['label' => __('transportation.title'), 'url' => route('operations.transportation.index')],
        ['label' => $trip->trip_code],
    ]"
/>

<x-card>
    <form method="POST" action="{{ route('operations.transportation.update', $trip) }}">
        @csrf @method('PUT')
        @include('pages.transportation.partials.form', [
            'trip' => $trip,
            'campaigns' => $campaigns,
            'locations' => $locations,
            'tripTypes' => $tripTypes,
            'selectedCampaignId' => $trip->campaign_id,
            'formAction' => route('operations.transportation.edit', $trip),
        ])
        <div class="d-flex gap-2 mt-3">
            <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>{{ __('common.save') }}</button>
            <a href="{{ route('operations.transportation.show', $trip) }}" class="btn btn-light">{{ __('common.cancel') }}</a>
        </div>
    </form>
</x-card>
@endsection
