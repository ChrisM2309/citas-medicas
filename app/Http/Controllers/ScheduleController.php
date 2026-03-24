<?php

namespace App\Http\Controllers;

use App\Http\Requests\Schedules\StoreScheduleRequest;
use App\Http\Requests\Schedules\UpdateScheduleRequest;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ScheduleController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Schedule::class, 'schedule');
    }

    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        return response()->json(
            $this->visibleSchedulesQuery($user)
                ->latest()
                ->get()
        );
    }

    public function store(StoreScheduleRequest $request)
    {
        $schedule = Schedule::create($request->validated());

        return response()->json($schedule, 201);
    }

    public function show(Schedule $schedule)
    {
        return response()->json($schedule);
    }

    public function update(UpdateScheduleRequest $request, Schedule $schedule)
    {
        $schedule->update($request->validated());

        return response()->json($schedule);
    }

    public function destroy(Schedule $schedule)
    {
        $schedule->delete();

        return response()->json([
            'message' => 'Horario eliminado correctamente',
        ]);
    }

    public function doctorSchedules($doctorId)
    {
        Gate::authorize('viewDoctorSchedules', [Schedule::class, (int) $doctorId]);

        /** @var User $user */
        $user = Auth::user();

        $schedules = $this->visibleSchedulesQuery($user)
            ->where('doctor_id', $doctorId)
            ->latest()
            ->get();

        return response()->json($schedules);
    }

    private function visibleSchedulesQuery(User $user): Builder
    {
        $query = Schedule::query();

        if ($user->hasAnyPermission(['read_all_appointments', 'manage_appointments'])) {
            return $query;
        }

        if ($user->hasPermissionTo('read_own_appointments')) {
            return $query->where('doctor_id', $user->doctor?->id ?? 0);
        }

        return $query->whereRaw('1 = 0');
    }
}