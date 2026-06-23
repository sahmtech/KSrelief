<?php

namespace App\Support;

final class ClinicalValuePresenter
{
    /**
     * @return array{is_link: bool, url: ?string, label: string, text: string, icon: string, variant: string}
     */
    public static function present(mixed $value, ?string $type = null, ?string $fieldLabel = null): array
    {
        $text = trim((string) ($value ?? ''));

        if ($text === '') {
            return self::plain('—');
        }

        $url = self::resolveUrl($text, $type);

        if ($url !== null) {
            $variant = self::linkVariant($url);
            $defaultLabel = self::linkLabel($url);

            return [
                'is_link' => true,
                'url' => $url,
                'label' => filled($fieldLabel) ? trim($fieldLabel) : $defaultLabel,
                'text' => $text,
                'icon' => self::linkIcon($url),
                'variant' => $variant,
            ];
        }

        return self::plain($text);
    }

    public static function isLinkable(mixed $value, ?string $type = null): bool
    {
        return self::present($value, $type)['is_link'];
    }

    /**
     * @return array{is_link: bool, url: ?string, label: string, text: string, icon: string, variant: string}
     */
    private static function plain(string $text): array
    {
        return [
            'is_link' => false,
            'url' => null,
            'label' => $text,
            'text' => $text,
            'icon' => 'link',
            'variant' => 'default',
        ];
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
        $text = trim($text);

        if (preg_match('#\bhttps?://[^\s<>"\'\)\]]+#i', $text, $matches)) {
            $candidate = rtrim($matches[0], '.,;)]');

            if (filter_var($candidate, FILTER_VALIDATE_URL)) {
                return $candidate;
            }
        }

        if (preg_match('#(?:drive|docs)\.google\.com/[^\s<>"\'\)\]]+#i', $text, $matches)) {
            return self::normalizeUrl('https://'.rtrim($matches[0], '.,;)]'));
        }

        if (filter_var($text, FILTER_VALIDATE_URL)) {
            return $text;
        }

        return null;
    }

    private static function linkLabel(string $url): string
    {
        $host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');

        return match (true) {
            self::isGoogleDriveHost($host) => __('workflow.links.google_drive'),
            str_contains($host, 'youtube.com'),
            str_contains($host, 'youtu.be') => __('workflow.links.video'),
            default => __('patients.clinical.open_link'),
        };
    }

    private static function linkIcon(string $url): string
    {
        $host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');

        return match (true) {
            self::isGoogleDriveHost($host) => 'brand-google-drive',
            str_contains($host, 'youtube.com'),
            str_contains($host, 'youtu.be') => 'brand-youtube',
            default => 'external-link',
        };
    }

    private static function linkVariant(string $url): string
    {
        $host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');

        return match (true) {
            self::isGoogleDriveHost($host) => 'drive',
            str_contains($host, 'youtube.com'),
            str_contains($host, 'youtu.be') => 'video',
            default => 'external',
        };
    }

    private static function isGoogleDriveHost(string $host): bool
    {
        return str_contains($host, 'drive.google')
            || str_contains($host, 'docs.google');
    }
}
