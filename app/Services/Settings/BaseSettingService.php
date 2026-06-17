<?php

namespace App\Services\Settings;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

abstract class BaseSettingService
{
    /**
     * @return class-string<Model>
     */
    abstract protected function modelClass(): string;

    /**
     * @return Builder<Model>
     */
    protected function newQuery(): Builder
    {
        return $this->modelClass()::query();
    }

    protected function resolveUserId(?int $userId = null): ?int
    {
        return $userId ?? Auth::id();
    }

    public function create(array $data, ?int $userId = null): Model
    {
        $userId = $this->resolveUserId($userId);

        return $this->newQuery()->create([
            ...$data,
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);
    }

    public function update(Model $model, array $data, ?int $userId = null): Model
    {
        $model->update([
            ...$data,
            'updated_by' => $this->resolveUserId($userId),
        ]);

        return $model->fresh();
    }

    public function delete(Model $model): void
    {
        $model->delete();
    }

    public function restore(Model $model): Model
    {
        $model->restore();

        return $model->fresh();
    }
}
