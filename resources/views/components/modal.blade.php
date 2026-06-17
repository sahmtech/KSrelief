@props([
    'id',
    'title',
    'size' => null,
])

<div {{ $attributes->merge(['class' => 'modal fade modal-admin', 'id' => $id, 'tabindex' => '-1', 'aria-labelledby' => $id.'Label', 'aria-hidden' => 'true']) }}>
    <div @class([
        'modal-dialog',
        'modal-sm' => $size === 'sm',
        'modal-lg' => $size === 'lg',
        'modal-xl' => $size === 'xl',
    ])>
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $id }}Label">{{ $title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('common.close') }}"></button>
            </div>
            <div class="modal-body">
                {{ $slot }}
            </div>
            @if(isset($footer))
                <div class="modal-footer">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>
