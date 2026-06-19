<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\GalleryImage;
use App\Models\Pdf;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CourseMediaController extends Controller
{
    public function storeImage(Request $request, Course $course)
    {
        if (!$request->user()->hasPermission('create_users')) {
            abort(403);
        }

        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'image' => ['required', 'image', 'max:8192'],
        ]);

        $path = $request->file('image')->store("courses/{$course->id}/gallery", 'public');

        GalleryImage::create([
            'title' => $data['title'] ?? null,
            'path' => $path,
            'course_id' => $course->id,
        ]);

        return back()->with('status', __('Image uploaded.'));
    }

    public function storePdf(Request $request, Course $course)
    {
        if (!$request->user()->hasPermission('create_users')) {
            abort(403);
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'file' => ['required', 'file', 'mimes:pdf', 'max:20480'],
        ]);

        $file = $request->file('file');
        $path = $file->store("courses/{$course->id}/pdfs", 'public');

        Pdf::create([
            'title' => $data['title'],
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'course_id' => $course->id,
        ]);

        return back()->with('status', __('PDF uploaded.'));
    }

    public function storeVideo(Request $request, Course $course)
    {
        if (!$request->user()->hasPermission('create_users')) {
            abort(403);
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'youtube_url' => ['required', 'url', 'max:2048'],
        ]);

        $video = new Video($data + ['course_id' => $course->id]);

        if (!$video->youtubeId()) {
            return back()->withErrors(['youtube_url' => __('That does not look like a valid YouTube link.')])->withInput();
        }

        $video->save();

        return back()->with('status', __('Video added.'));
    }

    public function toggleFeatured(Request $request, GalleryImage $image)
    {
        if (!$request->user()->hasPermission('create_users')) {
            abort(403);
        }

        $image->update(['is_featured' => !$image->is_featured]);

        return back()->with('status', __('Featured status updated.'));
    }

    public function destroyImage(Request $request, GalleryImage $image)
    {
        if (!$request->user()->hasPermission('delete_users')) {
            abort(403);
        }

        Storage::disk('public')->delete($image->path);
        $image->delete();

        return back()->with('status', __('Image deleted.'));
    }

    public function destroyPdf(Request $request, Pdf $pdf)
    {
        if (!$request->user()->hasPermission('delete_users')) {
            abort(403);
        }

        Storage::disk('public')->delete($pdf->path);
        $pdf->delete();

        return back()->with('status', __('PDF deleted.'));
    }

    public function destroyVideo(Request $request, Video $video)
    {
        if (!$request->user()->hasPermission('delete_users')) {
            abort(403);
        }

        $video->delete();

        return back()->with('status', __('Video deleted.'));
    }
}
