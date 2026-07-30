<?php

namespace App\Support;

use App\Models\CtFindingOption;
use App\Models\MriFindingOption;

final class ImagingFindingsTreeSupport
{
    /**
     * @return list<array{key: string, label_key: string, children?: list<array<string, mixed>>}>
     */
    public static function tree(string $modality): array
    {
        $modality = self::normalizeModality($modality);

        return config("imaging_findings_tree.{$modality}", []);
    }

    /**
     * @return list<string>
     */
    public static function modalities(): array
    {
        return ['ct', 'mri'];
    }

    /**
     * @return list<string>
     */
    public static function ears(): array
    {
        return ['right', 'left'];
    }

    /**
     * @return array<string, array{label_key: string, parent: ?string, is_leaf: bool, depth: int}>
     */
    public static function nodeIndex(string $modality): array
    {
        $modality = self::normalizeModality($modality);
        $index = [];

        $walk = function (array $nodes, ?string $parent, int $depth) use (&$walk, &$index): void {
            foreach ($nodes as $node) {
                $key = (string) ($node['key'] ?? '');
                if ($key === '') {
                    continue;
                }

                $path = $parent === null ? $key : "{$parent}.{$key}";
                $children = $node['children'] ?? [];
                $isLeaf = $children === [];

                $index[$path] = [
                    'label_key' => (string) ($node['label_key'] ?? $key),
                    'parent' => $parent,
                    'is_leaf' => $isLeaf,
                    'depth' => $depth,
                ];

                if ($children !== []) {
                    $walk($children, $path, $depth + 1);
                }
            }
        };

        $walk(self::tree($modality), null, 0);

        return $index;
    }

    public static function isValidKey(string $modality, string $path): bool
    {
        return array_key_exists($path, self::nodeIndex($modality));
    }

    public static function isLeaf(string $modality, string $path): bool
    {
        return (self::nodeIndex($modality)[$path]['is_leaf'] ?? false) === true;
    }

    /**
     * @return list<string>
     */
    public static function leafKeys(string $modality, ?string $path = null): array
    {
        $modality = self::normalizeModality($modality);
        $index = self::nodeIndex($modality);

        if ($path === null) {
            return collect($index)
                ->filter(fn (array $node): bool => $node['is_leaf'])
                ->keys()
                ->values()
                ->all();
        }

        if (! array_key_exists($path, $index)) {
            return [];
        }

        $prefix = $path.'.';

        return collect($index)
            ->filter(fn (array $node, string $key): bool => $node['is_leaf'] && ($key === $path || str_starts_with($key, $prefix)))
            ->keys()
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    public static function descendantLeafKeys(string $modality, string $path): array
    {
        $modality = self::normalizeModality($modality);

        if (! self::isValidKey($modality, $path)) {
            return [];
        }

        if (self::isLeaf($modality, $path)) {
            return [$path];
        }

        $prefix = $path.'.';

        return collect(self::nodeIndex($modality))
            ->filter(fn (array $node, string $key): bool => $node['is_leaf'] && str_starts_with($key, $prefix))
            ->keys()
            ->values()
            ->all();
    }

    public static function label(string $modality, string $path): string
    {
        $labelKey = self::nodeIndex($modality)[$path]['label_key'] ?? $path;

        return str_contains($labelKey, '.') ? __($labelKey) : $labelKey;
    }

    /**
     * @param  list<mixed>  $values
     * @return list<string>
     */
    public static function normalizeSelection(string $modality, array $values): array
    {
        $modality = self::normalizeModality($modality);
        $normalized = [];

        foreach ($values as $value) {
            if (is_numeric($value)) {
                $mapped = self::migrateLegacyId($modality, (int) $value);
                array_push($normalized, ...$mapped);

                continue;
            }

            $key = trim((string) $value);
            if ($key === '') {
                continue;
            }

            if (self::isLeaf($modality, $key)) {
                $normalized[] = $key;

                continue;
            }

            if (self::isValidKey($modality, $key)) {
                array_push($normalized, ...self::descendantLeafKeys($modality, $key));
            }
        }

        return collect($normalized)
            ->filter(fn (string $key): bool => self::isValidKey($modality, $key) && self::isLeaf($modality, $key))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    public static function migrateLegacyId(string $modality, int $id): array
    {
        if ($id <= 0) {
            return [];
        }

        $modality = self::normalizeModality($modality);
        $modelClass = $modality === 'ct' ? CtFindingOption::class : MriFindingOption::class;
        $code = $modelClass::query()->whereKey($id)->value('code');

        if (! filled($code)) {
            return [];
        }

        $path = config("imaging_findings_tree.legacy_{$modality}_codes.{$code}");

        if (! filled($path)) {
            return [];
        }

        return self::normalizeSelection($modality, [(string) $path]);
    }

    /**
     * @param  list<string>  $keys
     * @return list<string>
     */
    public static function labelsForKeys(string $modality, array $keys): array
    {
        return collect(self::normalizeSelection($modality, $keys))
            ->map(fn (string $key): string => self::label($modality, $key))
            ->values()
            ->all();
    }

    /**
     * Hierarchical label, e.g. "Mastoid / middle ear with finding › Soft tissue density".
     */
    public static function breadcrumbLabel(string $modality, string $path, string $separator = ' › '): string
    {
        $modality = self::normalizeModality($modality);
        $index = self::nodeIndex($modality);
        $parts = [];
        $current = $path;

        while ($current !== null && isset($index[$current])) {
            array_unshift($parts, self::label($modality, $current));
            $current = $index[$current]['parent'] ?? null;
        }

        return $parts === [] ? $path : implode($separator, $parts);
    }

    /**
     * @param  list<string>  $keys
     * @return list<string>
     */
    public static function breadcrumbLabelsForKeys(string $modality, array $keys): array
    {
        return collect(self::normalizeSelection($modality, $keys))
            ->map(fn (string $key): string => self::breadcrumbLabel($modality, $key))
            ->values()
            ->all();
    }

    /**
     * @param  list<string>  $selectedLeafKeys
     */
    public static function nodeCheckState(string $modality, string $path, array $selectedLeafKeys): string
    {
        $leaves = self::descendantLeafKeys($modality, $path);
        if ($leaves === []) {
            return 'unchecked';
        }

        $selected = collect($selectedLeafKeys);
        $selectedCount = collect($leaves)->filter(fn (string $leaf): bool => $selected->contains($leaf))->count();

        if ($selectedCount === 0) {
            return 'unchecked';
        }

        if ($selectedCount === count($leaves)) {
            return 'checked';
        }

        return 'indeterminate';
    }

    private static function normalizeModality(string $modality): string
    {
        return in_array($modality, self::modalities(), true) ? $modality : 'ct';
    }
}
