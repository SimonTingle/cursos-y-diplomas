<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Instructor;
use App\Models\User;
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

    public function manage(Request $request, Course $course)
    {
        if (!$request->user()->hasPermission('create_users')) {
            abort(403, __('Unauthorized to manage courses'));
        }

        $instructors = Instructor::where('is_active', true)->orderBy('name')->get();
        $enrolledIds = $course->students()->pluck('users.id')->all();
        $availableStudents = User::where('role', 'student')
            ->whereNotIn('id', $enrolledIds)->orderBy('name')->get();

        return view('portal.courses.manage', [
            'course' => $course,
            'instructors' => $instructors,
            'availableStudents' => $availableStudents,
        ]);
    }

    public function enrollStudent(Request $request, Course $course)
    {
        if (!$request->user()->hasPermission('create_users')) {
            abort(403);
        }

        $data = $request->validate(['user_id' => ['required', 'exists:users,id']]);
        $course->students()->syncWithoutDetaching([$data['user_id']]);

        return back()->with('status', __('Student enrolled.'));
    }

    public function unenrollStudent(Request $request, Course $course, User $user)
    {
        if (!$request->user()->hasPermission('delete_users')) {
            abort(403);
        }

        $course->students()->detach($user->id);
        return back()->with('status', __('Student unenrolled.'));
    }

    public function assignInstructor(Request $request, Course $course)
    {
        if (!$request->user()->hasPermission('create_users')) {
            abort(403);
        }

        $data = $request->validate(['instructor_id' => ['nullable', 'exists:instructors,id']]);
        $course->update($data);

        return back()->with('status', __('Instructor assigned.'));
    }
}
