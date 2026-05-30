<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $courses = Course::withCount('students')
            ->orderByDesc('is_active')
            ->orderBy('starts_at')
            ->orderBy('title')
            ->get();

        $enrolledIds = $user->courses()->pluck('courses.id')->all();

        return view('portal.courses', [
            'isAdmin' => $user->isAdmin(),
            'courses' => $courses,
            'enrolledIds' => $enrolledIds,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'starts_at' => ['nullable', 'date'],
            'capacity' => ['nullable', 'integer', 'min:1'],
        ]);

        Course::create($data + ['is_active' => true]);

        return back()->with('status', __('Course added.'));
    }

    public function destroy(Course $course)
    {
        $course->delete();

        return back()->with('status', __('Course deleted.'));
    }

    public function enroll(Request $request, Course $course)
    {
        $request->user()->courses()->syncWithoutDetaching([$course->id]);

        return back()->with('status', __('You are enrolled.'));
    }

    public function unenroll(Request $request, Course $course)
    {
        $request->user()->courses()->detach($course->id);

        return back()->with('status', __('Enrollment cancelled.'));
    }
}
