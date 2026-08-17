<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Model;

class FileFolder extends Model
{
    use TenantScoped;

    protected $fillable = [
        'tenant_id', 'client_id', 'parent_folder_id', 'name', 'path',
        'is_system_folder', 'created_by',
    ];

    protected $casts = [
        'is_system_folder' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function parent()
    {
        return $this->belongsTo(FileFolder::class, 'parent_folder_id');
    }

    public function children()
    {
        return $this->hasMany(FileFolder::class, 'parent_folder_id')->orderBy('name');
    }

    public function files()
    {
        return $this->hasMany(File::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
