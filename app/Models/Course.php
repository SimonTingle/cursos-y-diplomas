<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $fillable = [
        'title', 'description', 'starts_at', 'capacity', 'is_active', 'instructor_id',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'enrollments')->withTimestamps();
    }

    public function isEnrolledBy(?User $user): bool
    {
        return $user !== null && $this->students()->whereKey($user->getKey())->exists();
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class)->withDefault();
    }

    public function galleryImages(): HasMany
    {
        return $this->hasMany(GalleryImage::class)->orderByDesc('is_featured')->orderByDesc('id');
    }

    public function pdfs(): HasMany
    {
        return $this->hasMany(Pdf::class)->orderByDesc('id');
    }

    public function videos(): HasMany
    {
        return $this->hasMany(Video::class)->orderByDesc('id');
    }
}
