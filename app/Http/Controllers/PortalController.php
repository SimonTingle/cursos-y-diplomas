<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;

class PortalController extends Controller
{
    public function index(Request $request)
    {
        // Resolve the month being viewed (?month=YYYY-MM), default to the current month.
        $month = $this->resolveMonth($request->query('month'));

        $events = Event::with('instructor')
            ->whereBetween('start_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
            ->orderBy('start_at')
            ->get();

        return view('portal.index', [
            'isAdmin' => $request->user()->isAdmin(),
            'monthLabel' => ucfirst($month->translatedFormat('F Y')),
            'prevMonth' => $month->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $month->copy()->addMonth()->format('Y-m'),
            'events' => $events,
            'photos' => $this->galleryPhotos(),
        ]);
    }

    protected function resolveMonth(?string $value): Carbon
    {
        if ($value && preg_match('/^\d{4}-\d{2}$/', $value)) {
            try {
                return Carbon::createFromFormat('Y-m', $value)->startOfMonth();
            } catch (\Throwable) {
                // fall through to current month
            }
        }

        return Carbon::now()->startOfMonth();
    }

    /**
     * Photos from the public gallery folder. Drop real .jpg/.png/.svg files in
     * public/images/portal-gallery and they appear automatically.
     */
    protected function galleryPhotos(): array
    {
        $dir = public_path('images/portal-gallery');

        if (! File::isDirectory($dir)) {
            return [];
        }

        return collect(File::files($dir))
            ->filter(fn ($f) => in_array(strtolower($f->getExtension()), ['jpg', 'jpeg', 'png', 'webp', 'svg', 'gif']))
            ->sortBy(fn ($f) => $f->getFilename())
            ->map(fn ($f) => asset('images/portal-gallery/'.$f->getFilename()))
            ->values()
            ->all();
    }
}
