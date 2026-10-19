<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DonationOption extends Model
{
    protected $fillable = [
        'title',
        'method',
        'description',
        'instructions',
        'payment_url',
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
