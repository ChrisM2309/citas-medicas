<?php

namespace App\Http\Controllers;
use App\Models\Schedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index()
    {
        return response()->json(Schedule::latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'doctor_id' => ['required', 'integer'],
            'day_of_week' => ['required', 'string', 'max:10'],
            'start_time' => ['required'],
            'end_time' => ['required'],
        ]);

        $schedule = Schedule::create($validated);

        return response()->json($schedule, 201);
    }

    public function show(Schedule $schedule)
    {
        return response()->json($schedule);
    }

    public function update(Request $request, Schedule $schedule)
    {
        $validated = $request->validate([
            'doctor_id' => ['sometimes', 'integer'],
            'day_of_week' => ['sometimes', 'string', 'max:10'],
            'start_time' => ['sometimes'],
            'end_time' => ['sometimes'],
        ]);

        $schedule->update($validated);

        return response()->json($schedule);
    }

    public function destroy(Schedule $schedule)
    {
        $schedule->delete();

        return response()->json([
            'message' => 'Horario eliminado correctamente'
        ]);
    }

    public function doctorSchedules($doctorId)
    {
        $schedules = Schedule::where('doctor_id', $doctorId)->get();

        return response()->json($schedules);
    }
}
