<?php

namespace App\Http\Controllers;

use App\Models\Instructor;

class CalendarController extends Controller
{
    public function index()
    {
        $instructors = Instructor::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'color']);

        $isAdmin = auth()->user()->isAdmin();

        return view('calendar.index', compact('instructors', 'isAdmin'));
    }
}
