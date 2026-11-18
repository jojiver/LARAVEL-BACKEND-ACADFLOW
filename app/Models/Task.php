<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    protected $fillable = [
        'user_id',
        'subject_id',
        'title',
        'type',
        'description',
        'deadline',
        'estimated_hours',
        'difficulty',
        'importance',
        'progress',
        'status',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'deadline' => 'datetime',
            'estimated_hours' => 'float',
            'progress' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function steps(): HasMany
    {
        return $this->hasMany(TaskStep::class)->orderBy('position');
    }
}
