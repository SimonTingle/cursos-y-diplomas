<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * FullCalendar event feed. Accepts ?start=&end= (ISO range) and ?instructor_id=.
     */
    public function index(Request $request)
    {
        $query = Event::query()->with('instructor');

        if ($request->filled('start')) {
            $query->where('start_at', '>=', $request->date('start'));
        }
        if ($request->filled('end')) {
            $query->where('start_at', '<=', $request->date('end'));
        }
        if ($request->filled('instructor_id')) {
            $query->where('instructor_id', $request->integer('instructor_id'));
        }

        return response()->json(
            $query->get()->map->toCalendarArray()
        );
    }

    public function store(Request $request)
    {
        $event = Event::create($this->validated($request));

        return response()->json($event->fresh('instructor')->toCalendarArray(), 201);
    }

    public function update(Request $request, Event $event)
    {
        $event->update($this->validated($request));

        return response()->json($event->fresh('instructor')->toCalendarArray());
    }

    public function destroy(Event $event)
    {
        $event->delete();

        return response()->json(['deleted' => true]);
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'instructor_id' => ['nullable', 'exists:instructors,id'],
            'start_at' => ['required', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'all_day' => ['boolean'],
            'location' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:9'],
            'status' => ['nullable', 'in:scheduled,cancelled,completed'],
        ]);
    }
}
