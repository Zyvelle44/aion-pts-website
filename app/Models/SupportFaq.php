<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportFaq extends Model
{
    protected $fillable = [
        'question',
        'answer',
        'category',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
