<?php

namespace App\Http\Controllers;

use App\Models\GalleryImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    public function index(Request $request)
    {
        return view('portal.gallery', [
            'isAdmin' => $request->user()->isAdmin(),
            'images' => GalleryImage::latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'image' => ['required', 'image', 'max:8192'],
        ]);

        $path = $request->file('image')->store('gallery', 'public');

        GalleryImage::create([
            'title' => $data['title'] ?? null,
            'path' => $path,
        ]);

        return back()->with('status', __('Image uploaded.'));
    }

    public function destroy(GalleryImage $image)
    {
        Storage::disk('public')->delete($image->path);
        $image->delete();

        return back()->with('status', __('Image deleted.'));
    }
}
