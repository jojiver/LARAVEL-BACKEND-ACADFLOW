<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiToken extends Model
{
    protected $fillable = [
        'user_id',
        'token_hash',
        'last_used_at',
    ];

    /** Defines attribute casting for this model. */
    protected function casts(): array
    {
        return [
            'last_used_at' => 'datetime',
        ];
    }

    /** Defines the user relationship. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
