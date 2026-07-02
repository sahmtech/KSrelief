<?php

namespace App\Http\Requests\Workflow\Concerns;

trait MergesMedicalRecordFieldInputs
{
    /**
     * Laravel does not expand `field_*` rule keys — dynamic stage fields must be merged manually.
     *
     * @return array<string, mixed>|mixed
     */
    public function validated($key = null, $default = null): mixed
    {
        $validated = array_merge(
            parent::validated(),
            collect($this->all())
                ->filter(fn (mixed $value, string $name): bool => str_starts_with($name, 'field_'))
                ->all()
        );

        if ($key === null) {
            return $validated;
        }

        return data_get($validated, $key, $default);
    }
}
