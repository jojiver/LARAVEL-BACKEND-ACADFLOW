<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassSchedule extends Model
{
    protected $fillable = [
        'user_id',
        'subject_id',
        'day',
        'start_time',
        'end_time',
        'room',
    ];

    /** Defines the user relationship. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Defines the subject relationship. */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
