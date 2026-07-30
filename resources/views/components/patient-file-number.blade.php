@props([
    'patient',
    'href' => null,
    'codeClass' => null,
])

@php
    $href = $href ?? route('patients.show', $patient);
@endphp

<x-record-code-link
    :href="$href"
    :code="$patient->file_number"
    :color="$patient->fileNumberAccentColor()"
    :code-class="$codeClass"
/>
