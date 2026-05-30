<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    protected $fillable = ['title', 'youtube_url'];

    /**
     * The YouTube video id parsed from common URL shapes.
     */
    public function youtubeId(): ?string
    {
        $url = $this->youtube_url ?? '';

        if (preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|shorts/|live/))([A-Za-z0-9_-]{11})~', $url, $m)) {
            return $m[1];
        }
        if (preg_match('~^[A-Za-z0-9_-]{11}$~', $url)) {
            return $url;
        }

        return null;
    }

    protected function embedUrl(): Attribute
    {
        return Attribute::get(function () {
            $id = $this->youtubeId();

            return $id ? "https://www.youtube.com/embed/{$id}" : null;
        });
    }
}
