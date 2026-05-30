<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;

class VideoController extends Controller
{
    public function index(Request $request)
    {
        return view('portal.videos', [
            'isAdmin' => $request->user()->isAdmin(),
            'videos' => Video::latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'youtube_url' => ['required', 'url', 'max:2048'],
        ]);

        $video = new Video($data);

        if (! $video->youtubeId()) {
            return back()->withErrors(['youtube_url' => __('That does not look like a valid YouTube link.')])->withInput();
        }

        $video->save();

        return back()->with('status', __('Video added.'));
    }

    public function destroy(Video $video)
    {
        $video->delete();

        return back()->with('status', __('Video deleted.'));
    }
}
