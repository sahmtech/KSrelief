<?php

namespace App\Support;

use App\Models\Campaign;
use App\Models\Country;
use App\Models\Patient;
use Carbon\CarbonInterface;
use Illuminate\Support\Str;

final class RecordCodeGenerator
{
    /** @var list<string> */
    private const STOP_WORDS = [
        'the', 'a', 'an', 'of', 'and', 'in', 'for', 'to',
        'ال', 'و', 'في', 'من', 'على', 'إلى', 'ل', 'ب',
    ];

    public function countryCode(?Country $country): string
    {
        if ($country === null) {
            return 'XX';
        }

        if (filled($country->iso2)) {
            return Str::upper((string) $country->iso2);
        }

        if (filled($country->iso3)) {
            return Str::upper(Str::substr((string) $country->iso3, 0, 3));
        }

        if (filled($country->code)) {
            $normalized = preg_replace('/[^A-Za-z0-9]/', '', (string) $country->code) ?? '';

            if ($normalized !== '') {
                return Str::upper(Str::substr($normalized, 0, 3));
            }
        }

        return $this->acronym((string) $country->name, 3);
    }

    public function campaignNameToken(string $name, int $length = 4): string
    {
        $name = trim($name);

        foreach ($this->words($name) as $word) {
            if (in_array(Str::lower($word), self::STOP_WORDS, true)) {
                continue;
            }

            $token = $this->tokenFromWord($word, $length);

            if ($token !== '') {
                return $token;
            }
        }

        return $this->tokenFromWord($name, $length) ?: 'CAMP';
    }

    public function generateCampaignCode(Campaign $campaign): string
    {
        $campaign->loadMissing('country');

        $base = sprintf(
            '%s-%s',
            $this->countryCode($campaign->country),
            $this->campaignNameToken((string) $campaign->name)
        );

        return $this->ensureUniqueCampaignCode($base, $campaign->id);
    }

    public function generatePatientFileNumber(
        Campaign $campaign,
        ?int $exceptPatientId = null,
        ?CarbonInterface $registeredAt = null
    ): string {
        $registeredAt ??= now();
        $prefix = $this->patientFileNumberBucketPrefix($campaign, $registeredAt);
        $sequence = $this->nextPatientSequenceForBucket($prefix, $exceptPatientId);

        return $prefix.$sequence;
    }

    public function patientCountryPrefix(?Country $country): string
    {
        $length = (int) config('patient_file_number.country_prefix_length', 3);

        if ($country === null) {
            return str_repeat('X', $length);
        }

        if (filled($country->iso3)) {
            $iso3 = Str::upper(preg_replace('/[^A-Za-z]/', '', (string) $country->iso3) ?? '');

            if ($iso3 !== '') {
                return Str::upper(Str::substr($iso3, 0, $length));
            }
        }

        if (filled($country->iso2)) {
            $iso2 = Str::upper(preg_replace('/[^A-Za-z]/', '', (string) $country->iso2) ?? '');

            if ($iso2 !== '') {
                return Str::upper(str_pad($iso2, $length, 'X'));
            }
        }

        return Str::upper(Str::substr($this->countryCode($country), 0, $length));
    }

    public function patientFileNumberBucketPrefix(Campaign $campaign, CarbonInterface $registeredAt): string
    {
        $campaign->loadMissing('country');

        return sprintf(
            '%s%s%s',
            $this->patientCountryPrefix($campaign->country),
            $registeredAt->format('Y'),
            $registeredAt->format('m'),
        );
    }

    public function isModernPatientFileNumber(?string $fileNumber): bool
    {
        if (! filled($fileNumber)) {
            return false;
        }

        $pattern = config('patient_file_number.pattern', '/^[A-Z]{3}\d{6}\d+$/');

        return (bool) preg_match($pattern, Str::upper(trim($fileNumber)));
    }

    /**
     * @return array{
     *     country_prefix: string,
     *     year: string,
     *     month: string,
     *     sequence: int,
     *     bucket_prefix: string,
     * }|null
     */
    public function describeModernPatientFileNumber(?string $fileNumber): ?array
    {
        if (! $this->isModernPatientFileNumber($fileNumber)) {
            return null;
        }

        $normalized = Str::upper(trim((string) $fileNumber));

        if (! preg_match('/^([A-Z]{3})(\d{4})(\d{2})(\d+)$/', $normalized, $matches)) {
            return null;
        }

        return [
            'country_prefix' => $matches[1],
            'year' => $matches[2],
            'month' => $matches[3],
            'sequence' => (int) $matches[4],
            'bucket_prefix' => $matches[1].$matches[2].$matches[3],
        ];
    }

    public function examplePatientFileNumber(?Country $country = null): string
    {
        $prefix = $this->patientCountryPrefix($country);

        return sprintf('%s%s%s1', $prefix, now()->format('Y'), now()->format('m'));
    }

    private function ensureUniqueCampaignCode(string $base, ?int $exceptId = null): string
    {
        $code = $base;
        $suffix = 2;

        while ($this->campaignCodeExists($code, $exceptId)) {
            $code = $base.$suffix;
            $suffix++;
        }

        return $code;
    }

    private function campaignCodeExists(string $code, ?int $exceptId): bool
    {
        return Campaign::query()
            ->where('code', $code)
            ->when($exceptId, fn ($query) => $query->where('id', '!=', $exceptId))
            ->exists();
    }

    private function nextPatientSequenceForBucket(string $bucketPrefix, ?int $exceptPatientId): int
    {
        $max = 0;

        Patient::query()
            ->withTrashed()
            ->where('file_number', 'like', $bucketPrefix.'%')
            ->when($exceptPatientId, fn ($query) => $query->where('id', '!=', $exceptPatientId))
            ->pluck('file_number')
            ->each(function (string $fileNumber) use ($bucketPrefix, &$max): void {
                if (! str_starts_with($fileNumber, $bucketPrefix)) {
                    return;
                }

                $suffix = Str::after($fileNumber, $bucketPrefix);

                if (ctype_digit($suffix)) {
                    $max = max($max, (int) $suffix);
                }
            });

        return $max + 1;
    }

    /**
     * @return list<string>
     */
    private function words(string $value): array
    {
        $value = preg_replace('/[()\[\]{}،,;:]+/u', ' ', $value) ?? $value;
        $value = preg_replace('/[\x{0640}\x{0610}-\x{061A}\x{064B}-\x{065F}\x{0670}\x{06D6}-\x{06ED}]/u', '', $value) ?? $value;

        return array_values(array_filter(preg_split('/[\s\-\/]+/u', trim($value), -1, PREG_SPLIT_NO_EMPTY) ?: []));
    }

    private function tokenFromWord(string $word, int $length): string
    {
        $transliterated = $this->transliterate($word);
        $alpha = preg_replace('/[^A-Za-z0-9]/', '', $transliterated) ?? '';

        if ($alpha === '') {
            $alpha = preg_replace('/[^A-Za-z0-9\x{0600}-\x{06FF}]/u', '', $word) ?? '';

            if ($alpha !== '') {
                $alpha = strtoupper(substr(md5($alpha), 0, $length));
            }
        }

        return Str::upper(Str::substr($alpha, 0, $length));
    }

    private function transliterate(string $value): string
    {
        if (class_exists(\Transliterator::class)) {
            $transliterator = \Transliterator::create('Any-Latin; Latin-ASCII; [:^Ascii:] Remove');

            if ($transliterator !== null) {
                $converted = $transliterator->transliterate($value);

                if (is_string($converted) && $converted !== '') {
                    return $converted;
                }
            }
        }

        return Str::ascii($value);
    }

    private function acronym(string $value, int $length): string
    {
        $words = $this->words($value);
        $letters = '';

        foreach ($words as $word) {
            $token = $this->tokenFromWord($word, 1);

            if ($token !== '') {
                $letters .= $token;
            }
        }

        if ($letters === '') {
            $letters = $this->tokenFromWord($value, $length);
        }

        return Str::upper(Str::substr($letters, 0, $length));
    }
}
