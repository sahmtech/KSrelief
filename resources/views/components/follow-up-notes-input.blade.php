@props([
    'namePrefix',
    'savedValue' => null,
])

@php
    $text = \App\Support\FollowUpFieldSupport::resolveNotesForForm(old($namePrefix, $savedValue));
@endphp

<textarea name="{{ $namePrefix }}"
          class="form-control follow-up-notes-textarea"
          rows="5"
          placeholder="{{ __('workflow.follow_up.notes_placeholder') }}">{{ $text }}</textarea>
