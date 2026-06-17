@extends('layouts.admin')

@section('title', __('locations.cities_for', ['country' => $country->localizedName()]))

@section('content')
<x-page-header
    :title="__('locations.cities_for', ['country' => $country->localizedName()])"
    :subtitle="__('locations.city_count', ['count' => $country->cities_count])"
    :breadcrumbs="[
        ['label' => __('menu.administration')],
        ['label' => __('locations.title'), 'url' => route('administration.locations.index')],
        ['label' => $country->localizedName()],
    ]"
/>

<div class="row g-3">
    <div class="col-lg-4">
        <x-card :title="__('locations.add_city_title')">
            <form id="addCityForm">
                <x-form-input :label="__('locations.fields.city_name')" name="name" id="city_name" required />
                <x-form-input :label="__('locations.fields.city_name_ar')" name="name_ar" id="city_name_ar" />
                <button type="submit" class="btn btn-primary w-100">
                    <i class="ti ti-plus me-1"></i> {{ __('locations.add_city') }}
                </button>
            </form>
            <div class="text-danger mt-2 d-none" id="addCityError"></div>
        </x-card>
    </div>
    <div class="col-lg-8">
        <x-card :title="__('locations.cities')" :flush="true">
            <div class="p-3 border-bottom">
                <form method="GET" class="row g-2 align-items-end">
                    <div class="col">
                        <input type="search" name="search" class="form-group-admin__input" value="{{ $search }}" placeholder="{{ __('locations.search_cities') }}">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary btn-sm">{{ __('campaigns.filters.apply') }}</button>
                    </div>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('locations.fields.city_name') }}</th>
                            <th>{{ __('locations.fields.city_name_ar') }}</th>
                        </tr>
                    </thead>
                    <tbody id="citiesTableBody">
                        @forelse($cities as $city)
                            <tr>
                                <td>{{ $city->name }}</td>
                                <td>{{ $city->name_ar ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center text-muted py-4">{{ __('locations.messages.no_cities') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($cities->hasPages())
                <div class="admin-card__footer">{{ $cities->links() }}</div>
            @endif
        </x-card>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('addCityForm')?.addEventListener('submit', async (event) => {
        event.preventDefault();
        const errorEl = document.getElementById('addCityError');
        const name = document.getElementById('city_name').value.trim();
        const nameAr = document.getElementById('city_name_ar').value.trim();
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

        const response = await fetch(@json(route('locations.cities.store', $country)), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify({ name, name_ar: nameAr || null }),
        });

        const json = await response.json();

        if (!response.ok) {
            const firstError = json.errors ? Object.values(json.errors)[0]?.[0] : null;
            errorEl.textContent = firstError || json.message || 'Error';
            errorEl.classList.remove('d-none');
            return;
        }

        errorEl.classList.add('d-none');
        window.location.reload();
    });
</script>
@endpush
