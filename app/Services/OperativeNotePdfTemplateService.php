<?php

namespace App\Services;

use App\Models\OperativeNotePdfTemplate;
use App\Models\User;
use App\Support\OperativeNotePdfTemplateCatalog;
use Illuminate\Support\Arr;

final class OperativeNotePdfTemplateService
{
    /**
     * @return array{
     *     defaults: array<string, string>,
     *     narrative_paragraphs: list<string>,
     *     post_op_orders: list<string>,
     *     formatting: array<string, float|int|bool>,
     *     is_custom: bool,
     *     updated_at: ?string,
     *     updated_by_name: ?string
     * }
     */
    public function current(): array
    {
        $row = OperativeNotePdfTemplate::query()->with('updater')->latest('id')->first();
        $payload = $this->normalize($row?->payload_json ?? null);

        return array_merge($payload, [
            'is_custom' => $row !== null,
            'updated_at' => $row?->updated_at?->format('d M Y, H:i'),
            'updated_by_name' => $row?->updater?->name,
        ]);
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array{
     *     defaults: array<string, string>,
     *     narrative_paragraphs: list<string>,
     *     post_op_orders: list<string>,
     *     formatting: array<string, float|int|bool>
     * }
     */
    public function save(array $input, User $user): array
    {
        $payload = $this->normalize($input);

        $row = OperativeNotePdfTemplate::query()->latest('id')->first();

        if ($row) {
            $row->update([
                'payload_json' => $payload,
                'updated_by' => $user->id,
            ]);
        } else {
            OperativeNotePdfTemplate::query()->create([
                'payload_json' => $payload,
                'updated_by' => $user->id,
            ]);
        }

        return $payload;
    }

    public function resetToDefaults(User $user): array
    {
        return $this->save(OperativeNotePdfTemplateCatalog::defaults(), $user);
    }

    /**
     * @param  array<string, mixed>|null  $input
     * @return array{
     *     defaults: array<string, string>,
     *     narrative_paragraphs: list<string>,
     *     post_op_orders: list<string>,
     *     formatting: array<string, float|int|bool>
     * }
     */
    public function normalize(?array $input): array
    {
        $base = OperativeNotePdfTemplateCatalog::defaults();

        if ($input === null) {
            return $base;
        }

        $defaults = array_merge(
            $base['defaults'],
            Arr::only((array) ($input['defaults'] ?? []), array_keys($base['defaults']))
        );

        foreach ($defaults as $key => $value) {
            $defaults[$key] = trim((string) $value);
        }

        $paragraphs = collect($input['narrative_paragraphs'] ?? $base['narrative_paragraphs'])
            ->map(fn ($p) => trim((string) $p))
            ->filter(fn (string $p) => $p !== '')
            ->values()
            ->all();

        if ($paragraphs === []) {
            $paragraphs = $base['narrative_paragraphs'];
        }

        $orders = collect($input['post_op_orders'] ?? $base['post_op_orders'])
            ->map(fn ($o) => trim((string) $o))
            ->filter(fn (string $o) => $o !== '')
            ->values()
            ->all();

        if ($orders === []) {
            $orders = $base['post_op_orders'];
        }

        $formattingInput = (array) ($input['formatting'] ?? []);
        $formatting = $base['formatting'];
        $formatting['page_margin_mm'] = $this->clampFloat($formattingInput['page_margin_mm'] ?? $formatting['page_margin_mm'], 5, 20);
        $formatting['border_width_pt'] = $this->clampFloat($formattingInput['border_width_pt'] ?? $formatting['border_width_pt'], 0.5, 5);
        $formatting['page_padding_bottom_mm'] = $this->clampFloat($formattingInput['page_padding_bottom_mm'] ?? $formatting['page_padding_bottom_mm'], 8, 40);
        $formatting['body_font_size_pt'] = $this->clampFloat($formattingInput['body_font_size_pt'] ?? $formatting['body_font_size_pt'], 9, 14);
        $formatting['title_font_size_pt'] = $this->clampFloat($formattingInput['title_font_size_pt'] ?? $formatting['title_font_size_pt'], 12, 22);
        $formatting['show_logo'] = filter_var($formattingInput['show_logo'] ?? $formatting['show_logo'], FILTER_VALIDATE_BOOLEAN);

        return [
            'defaults' => $defaults,
            'narrative_paragraphs' => $paragraphs,
            'post_op_orders' => $orders,
            'formatting' => $formatting,
        ];
    }

    /**
     * Replace {{tokens}} in a paragraph with HTML-safe filled values.
     *
     * @param  array<string, string>  $replacements  token => plain text value
     */
    public function renderParagraph(string $paragraph, array $replacements): string
    {
        $html = e($paragraph);

        foreach ($replacements as $token => $value) {
            $safeToken = e($token);
            $replacement = $value !== ''
                ? '<span class="fill">'.e($value).'</span>'
                : '....................';

            $html = str_replace($safeToken, $replacement, $html);
        }

        return $html;
    }

    private function clampFloat(mixed $value, float $min, float $max): float
    {
        $n = is_numeric($value) ? (float) $value : $min;

        return max($min, min($max, $n));
    }
}
