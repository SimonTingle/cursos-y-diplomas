<?php

namespace App\Http\Controllers;

use App\Models\Instructor;
use Illuminate\Http\Request;

class InstructorController extends Controller
{
    public function index()
    {
        return response()->json(
            Instructor::orderBy('name')->get(['id', 'name', 'title', 'color', 'is_active'])
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'title' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string'],
            'color' => ['nullable', 'string', 'max:9'],
            'is_active' => ['boolean'],
        ]);

        $instructor = Instructor::create($data);

        return response()->json($instructor, 201);
    }
}
