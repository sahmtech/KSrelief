<?php

namespace App\Support;

final class ClinicalValuePresenter
{
    /**
     * @return array{is_link: bool, url: ?string, label: string, text: string, icon: string}
     */
    public static function present(mixed $value, ?string $type = null): array
    {
        $text = trim((string) ($value ?? ''));

        if ($text === '') {
            return [
                'is_link' => false,
                'url' => null,
                'label' => '—',
                'text' => '—',
                'icon' => 'link',
            ];
        }

        $url = self::resolveUrl($text, $type);

        if ($url !== null) {
            return [
                'is_link' => true,
                'url' => $url,
                'label' => self::linkLabel($url),
                'text' => $text,
                'icon' => self::linkIcon($url),
            ];
        }

        return [
            'is_link' => false,
            'url' => null,
            'label' => $text,
            'text' => $text,
            'icon' => 'link',
        ];
    }

    public static function isLinkable(mixed $value, ?string $type = null): bool
    {
        return self::present($value, $type)['is_link'];
    }

    private static function resolveUrl(string $text, ?string $type): ?string
    {
        if ($type === 'url') {
            return self::normalizeUrl($text);
        }

        return self::normalizeUrl($text);
    }

    private static function normalizeUrl(string $text): ?string
    {
        if (filter_var($text, FILTER_VALIDATE_URL)) {
            return $text;
        }

        if (preg_match('/^drive\.google\.com/i', $text) || preg_match('/^docs\.google\.com/i', $text)) {
            return 'https://'.$text;
        }

        if (preg_match('#^https?://#i', $text)) {
            return filter_var($text, FILTER_VALIDATE_URL) ? $text : null;
        }

        return null;
    }

    private static function linkLabel(string $url): string
    {
        $host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');

        return match (true) {
            str_contains($host, 'drive.google'),
            str_contains($host, 'docs.google') => __('workflow.links.google_drive'),
            str_contains($host, 'youtube.com'),
            str_contains($host, 'youtu.be') => __('workflow.links.video'),
            default => __('patients.clinical.open_link'),
        };
    }

    private static function linkIcon(string $url): string
    {
        $host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');

        return match (true) {
            str_contains($host, 'drive.google'),
            str_contains($host, 'docs.google') => 'brand-google-drive',
            str_contains($host, 'youtube.com'),
            str_contains($host, 'youtu.be') => 'brand-youtube',
            default => 'external-link',
        };
    }
}
