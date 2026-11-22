<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'instructor',
        'room',
        'schedule',
        'category',
        'color',
    ];

    /** Defines the user relationship. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Defines the tasks relationship. */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /** Defines the classSchedules relationship. */
    public function classSchedules(): HasMany
    {
        return $this->hasMany(ClassSchedule::class);
    }
}
