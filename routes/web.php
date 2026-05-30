<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VideoController;
use App\Models\Course;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // Signed-in users go to their portal.
    if (auth()->check()) {
        return redirect()->route('portal');
    }

    // Guests see the public landing with a showcase gallery + next upcoming course.
    $dir = public_path('images/portal-gallery');
    $photos = File::isDirectory($dir)
        ? collect(File::files($dir))
            ->filter(fn ($f) => in_array(strtolower($f->getExtension()), ['jpg', 'jpeg', 'png', 'webp', 'svg', 'gif']))
            ->sortBy(fn ($f) => $f->getFilename())
            ->map(fn ($f) => asset('images/portal-gallery/'.$f->getFilename()))
            ->values()
            ->all()
        : [];

    $nextCourse = Course::where('is_active', true)
        ->whereNotNull('starts_at')
        ->where('starts_at', '>=', now())
        ->orderBy('starts_at')
        ->first();

    return view('welcome', compact('photos', 'nextCourse'));
})->name('home');

Route::middleware(['auth'])->group(function () {
    // Signed-in portal — the post-login home.
    Route::get('/portal', [PortalController::class, 'index'])->name('portal');

    // Instructors calendar — full calendar view (linked from the portal).
    Route::get('/instructors-calendar', [CalendarController::class, 'index'])
        ->name('instructors-calendar');

    Route::get('/dashboard', fn () => redirect()->route('portal'))
        ->name('dashboard');

    // Calendar data API (CSRF-protected via web middleware).
    // Reading is open to any authenticated user; writes require the admin role.
    Route::get('/events', [EventController::class, 'index'])->name('events.index');
    Route::get('/instructors', [InstructorController::class, 'index'])->name('instructors.index');

    Route::middleware('can:admin')->group(function () {
        Route::post('/events', [EventController::class, 'store'])->name('events.store');
        Route::put('/events/{event}', [EventController::class, 'update'])->name('events.update');
        Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');
        Route::post('/instructors', [InstructorController::class, 'store'])->name('instructors.store');

        // Admin user management (accessible from profile page).
        Route::post('/admin/users', [AdminController::class, 'storeUser'])->name('admin.users.store');
        Route::delete('/admin/users/{targetUser}', [AdminController::class, 'destroyUser'])->name('admin.users.destroy');
    });

    // ---- Portal content sections ----
    // Courses (cursos): read + enroll open to any authenticated user.
    Route::get('/portal/cursos', [CourseController::class, 'index'])->name('portal.courses');
    Route::post('/portal/cursos/{course}/inscribirse', [CourseController::class, 'enroll'])->name('portal.courses.enroll');
    Route::delete('/portal/cursos/{course}/inscribirse', [CourseController::class, 'unenroll'])->name('portal.courses.unenroll');

    // Videoteca, PDF catalogue, image gallery: read open to all.
    Route::get('/portal/videoteca', [VideoController::class, 'index'])->name('portal.videos');
    Route::get('/portal/pdf', [PdfController::class, 'index'])->name('portal.pdfs');
    Route::get('/portal/galeria', [GalleryController::class, 'index'])->name('portal.gallery');

    // Admin-only content management for the four sections.
    Route::middleware('can:admin')->group(function () {
        Route::post('/portal/cursos', [CourseController::class, 'store'])->name('portal.courses.store');
        Route::delete('/portal/cursos/{course}', [CourseController::class, 'destroy'])->name('portal.courses.destroy');

        Route::post('/portal/videoteca', [VideoController::class, 'store'])->name('portal.videos.store');
        Route::delete('/portal/videoteca/{video}', [VideoController::class, 'destroy'])->name('portal.videos.destroy');

        Route::post('/portal/pdf', [PdfController::class, 'store'])->name('portal.pdfs.store');
        Route::delete('/portal/pdf/{pdf}', [PdfController::class, 'destroy'])->name('portal.pdfs.destroy');

        Route::post('/portal/galeria', [GalleryController::class, 'store'])->name('portal.gallery.store');
        Route::delete('/portal/galeria/{image}', [GalleryController::class, 'destroy'])->name('portal.gallery.destroy');
    });

    // Profile (from Breeze).
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
