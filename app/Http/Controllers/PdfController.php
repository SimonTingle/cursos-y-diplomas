<?php

namespace App\Http\Controllers;

use App\Models\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PdfController extends Controller
{
    public function index(Request $request)
    {
        return view('portal.pdfs', [
            'isAdmin' => $request->user()->isAdmin(),
            'pdfs' => Pdf::latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'file' => ['required', 'file', 'mimes:pdf', 'max:20480'],
        ]);

        $file = $request->file('file');
        $path = $file->store('pdfs', 'public');

        Pdf::create([
            'title' => $data['title'],
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
        ]);

        return back()->with('status', __('PDF uploaded.'));
    }

    public function destroy(Pdf $pdf)
    {
        Storage::disk('public')->delete($pdf->path);
        $pdf->delete();

        return back()->with('status', __('PDF deleted.'));
    }
}
