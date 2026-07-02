@props([
    'namePrefix',
    'savedValue' => null,
])

@php
    $text = \App\Support\PostOperationFieldSupport::resolveNotesForForm(old($namePrefix, $savedValue));
@endphp

<textarea name="{{ $namePrefix }}"
          class="form-control follow-up-notes-textarea"
          rows="5"
          placeholder="{{ __('workflow.post_op.notes_placeholder') }}">{{ $text }}</textarea>
