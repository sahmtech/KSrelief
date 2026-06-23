<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PatientAttachment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'patient_id',
        'original_name',
        'file_name',
        'file_type',
        'file_size',
        'storage_path',
        'uploaded_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function humanFileSize(): string
    {
        $bytes = $this->file_size;

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1).' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1).' KB';
        }

        return $bytes.' B';
    }

    public function isImage(): bool
    {
        return Str::startsWith((string) $this->file_type, 'image/');
    }

    public function isVideo(): bool
    {
        return Str::startsWith((string) $this->file_type, 'video/');
    }

    public function isPreviewable(): bool
    {
        return $this->isImage() || $this->isVideo();
    }

    public function iconClass(): string
    {
        if ($this->isImage()) {
            return 'ti-photo';
        }

        if ($this->isVideo()) {
            return 'ti-video';
        }

        if (Str::contains((string) $this->file_type, 'pdf')) {
            return 'ti-file-type-pdf';
        }

        return 'ti-file';
    }

    public function deleteStoredFile(): void
    {
        if (Storage::disk('local')->exists($this->storage_path)) {
            Storage::disk('local')->delete($this->storage_path);
        }
    }
}
