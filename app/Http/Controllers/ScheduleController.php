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
    public function index()
    {
        Gate::authorize('viewAny', Schedule::class);

        /** @var User $user */
        $user = Auth::user();

        return response()->json(
            $this->visibleSchedulesQuery($user)
                ->latest()
                ->get(),
        );
    }

    public function store(StoreScheduleRequest $request)
    {
        Gate::authorize('create', Schedule::class);

        $schedule = Schedule::create($request->validated());

        return response()->json($schedule, 201);
    }

    public function show(Schedule $schedule)
    {
        Gate::authorize('view', $schedule);

        return response()->json($schedule);
    }

    public function update(UpdateScheduleRequest $request, Schedule $schedule)
    {
        Gate::authorize('update', $schedule);

        $schedule->update($request->validated());

        return response()->json($schedule);
    }

    public function destroy(Schedule $schedule)
    {
        Gate::authorize('delete', $schedule);

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

        if ($user->hasPermissionTo('read_appointments') && ! $user->hasPermissionTo('read_all_appointments')) {
            return $query->where('doctor_id', $user->doctor?->id ?? 0);
        }

        return $query;
    }
}
