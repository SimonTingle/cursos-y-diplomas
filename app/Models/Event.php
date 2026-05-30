<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    protected $fillable = [
        'title', 'description', 'instructor_id', 'start_at', 'end_at',
        'all_day', 'location', 'color', 'status',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'all_day' => 'boolean',
    ];

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    /**
     * Shape this event for the FullCalendar event feed.
     */
    public function toCalendarArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'start' => optional($this->start_at)->toIso8601String(),
            'end' => optional($this->end_at)->toIso8601String(),
            'allDay' => $this->all_day,
            'backgroundColor' => $this->color ?: optional($this->instructor)->color ?: '#6366f1',
            'borderColor' => $this->color ?: optional($this->instructor)->color ?: '#6366f1',
            'extendedProps' => [
                'description' => $this->description,
                'location' => $this->location,
                'status' => $this->status,
                'instructor_id' => $this->instructor_id,
                'instructor' => optional($this->instructor)->name,
            ],
        ];
    }
}
