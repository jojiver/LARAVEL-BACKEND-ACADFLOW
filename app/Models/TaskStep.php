<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskStep extends Model
{
    protected $fillable = [
        'task_id',
        'label',
        'done',
        'position',
    ];

    /** Defines attribute casting for this model. */
    protected function casts(): array
    {
        return [
            'done' => 'boolean',
            'position' => 'integer',
        ];
    }

    /** Performs task in TaskStep. */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
