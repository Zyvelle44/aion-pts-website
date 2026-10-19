<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DownloadItem extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'type',
        'version',
        'description',
        'download_url',
        'file_size_bytes',
        'sha256',
        'release_notes',
        'is_active',
        'sort_order',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'file_size_bytes' => 'integer',
            'sort_order' => 'integer',
            'published_at' => 'datetime',
        ];
    }
}
