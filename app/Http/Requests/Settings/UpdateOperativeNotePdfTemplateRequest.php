<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOperativeNotePdfTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('operative_note_pdf.update') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'defaults' => ['required', 'array'],
            'defaults.preop_diagnosis' => ['required', 'string', 'max:255'],
            'defaults.postop_diagnosis' => ['required', 'string', 'max:255'],
            'defaults.facial_nerve_monitor' => ['required', 'string', 'max:120'],
            'defaults.local_anesthesia' => ['required', 'string', 'max:80'],
            'defaults.bed_status' => ['required', 'string', 'max:80'],
            'narrative_paragraphs' => ['required', 'array', 'min:1'],
            'narrative_paragraphs.*' => ['nullable', 'string', 'max:5000'],
            'post_op_orders' => ['required', 'array', 'min:1'],
            'post_op_orders.*' => ['nullable', 'string', 'max:500'],
            'formatting' => ['required', 'array'],
            'formatting.page_margin_mm' => ['required', 'numeric', 'min:5', 'max:20'],
            'formatting.border_width_pt' => ['required', 'numeric', 'min:0.5', 'max:5'],
            'formatting.page_padding_bottom_mm' => ['required', 'numeric', 'min:8', 'max:40'],
            'formatting.body_font_size_pt' => ['required', 'numeric', 'min:9', 'max:14'],
            'formatting.title_font_size_pt' => ['required', 'numeric', 'min:12', 'max:22'],
            'formatting.show_logo' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'formatting' => array_merge($this->input('formatting', []), [
                'show_logo' => $this->boolean('formatting.show_logo'),
            ]),
        ]);
    }
}
