@props([
    'id' => 'dataTable',
    'options' => [],
])

<div {{ $attributes->merge(['class' => 'datatable-wrapper']) }}>
    <table
        id="{{ $id }}"
        class="table table-hover w-100"
        data-datatable
        @if(count($options))
            data-datatable-options="{{ json_encode($options) }}"
        @endif
    >
        @if(isset($head))
            <thead>
                {{ $head }}
            </thead>
        @endif
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>
