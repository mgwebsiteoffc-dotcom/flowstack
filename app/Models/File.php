<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    use SoftDeletes, TenantScoped;

    protected $fillable = [
        'tenant_id', 'folder_id', 'client_id', 'original_name', 'stored_name',
        'file_path', 'file_size', 'mime_type', 'extension', 'version',
        'parent_file_id', 'share_token', 'share_expires_at',
        'is_shared_with_client', 'uploaded_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'share_expires_at' => 'datetime',
        'is_shared_with_client' => 'boolean',
    ];

    public const ALLOWED_EXTENSIONS = [
        'jpg', 'jpeg', 'png', 'gif', 'svg', 'webp',
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'txt',
        'mp4', 'zip',
    ];

    public const MAX_SIZE = 10 * 1024 * 1024; // 10 MB
    public const MAX_VIDEO_SIZE = 50 * 1024 * 1024; // mp4 up to 50 MB

    public function folder()
    {
        return $this->belongsTo(FileFolder::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function parentFile()
    {
        return $this->belongsTo(File::class, 'parent_file_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function scopeSharedWithClient($query)
    {
        return $query->where('is_shared_with_client', true);
    }

    public function isImage(): bool
    {
        return in_array($this->extension, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'], true);
    }

    public function isPdf(): bool
    {
        return $this->extension === 'pdf';
    }

    public function isVideo(): bool
    {
        return $this->extension === 'mp4';
    }

    public function shareUrl(): ?string
    {
        if (! $this->share_token) {
            return null;
        }

        return route('files.shared', $this->share_token);
    }

    public function sizeHuman(): string
    {
        $bytes = (int) $this->file_size;

        if ($bytes >= 1024 * 1024 * 1024) {
            return round($bytes / 1024 / 1024 / 1024, 2).' GB';
        }

        if ($bytes >= 1024 * 1024) {
            return round($bytes / 1024 / 1024, 2).' MB';
        }

        if ($bytes >= 1024) {
            return round($bytes / 1024, 1).' KB';
        }

        return $bytes.' B';
    }
}
