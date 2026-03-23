<?php

namespace App\Filament\Pages;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\User;
use BackedEnum;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class DoctorAvailabilityCalendar extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::CalendarDays;

    protected static ?string $navigationLabel = 'Calendario';

    protected static ?string $title = 'Calendario de disponibilidad médica';

    protected static ?string $navigationGroup = 'Agenda';

    protected static ?string $slug = 'calendar';

    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.pages.doctor-availability-calendar';

    public ?int $doctorId = null;

    public string $month;

    /** @var array<int, string> */
    public array $doctorOptions = [];

    /** @var array<int, array<string, mixed>|null> */
    public array $calendarCells = [];

    public bool $doctorLocked = false;

    public ?string $selectedDoctorName = null;

    public function mount(): void
    {
        $this->month = now()->startOfMonth()->format('Y-m');
        $this->doctorLocked = $this->isDoctorScoped();
        $this->doctorOptions = $this->buildDoctorOptions();
        $this->doctorId = $this->resolveInitialDoctorId();

        $this->refreshCalendar();
    }

    public static function canAccess(): bool
    {
        $user = Filament::auth()->user();

        if (! $user instanceof User) {
            return false;
        }

        return $user->hasAnyPermission(['read_appointments', 'read_all_appointments', 'manage_appointments', 'manage_users']);
    }

    public function previousMonth(): void
    {
        $currentMonth = Carbon::createFromFormat('Y-m', $this->month)->startOfMonth();
        $this->month = $currentMonth->subMonth()->format('Y-m');

        $this->refreshCalendar();
    }

    public function nextMonth(): void
    {
        $currentMonth = Carbon::createFromFormat('Y-m', $this->month)->startOfMonth();
        $this->month = $currentMonth->addMonth()->format('Y-m');

        $this->refreshCalendar();
    }

    public function updatedDoctorId(): void
    {
        $this->refreshCalendar();
    }

    public function updatedMonth(string $value): void
    {
        if (! preg_match('/^\d{4}-\d{2}$/', $value)) {
            $this->month = now()->format('Y-m');
        }

        $this->refreshCalendar();
    }

    public function getMonthLabelProperty(): string
    {
        return Carbon::createFromFormat('Y-m', $this->month)
            ->locale('es')
            ->translatedFormat('F Y');
    }

    private function refreshCalendar(): void
    {
        if (! $this->doctorId) {
            $this->selectedDoctorName = null;
            $this->calendarCells = [];

            return;
        }

        $monthStart = Carbon::createFromFormat('Y-m', $this->month)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        $doctor = Doctor::query()
            ->with('user:id,name')
            ->find($this->doctorId);

        if (! $doctor) {
            $this->selectedDoctorName = null;
            $this->calendarCells = [];

            return;
        }

        $this->selectedDoctorName = $doctor->user?->name ?? "Doctor #{$doctor->id}";

        $schedulesByDay = $doctor->schedules()
            ->get()
            ->groupBy('day_of_week');

        $appointmentsByDate = Appointment::query()
            ->where('doctor_id', $doctor->id)
            ->whereBetween('appointment_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->where('status', '!=', 'canceled')
            ->get()
            ->groupBy('appointment_date');

        $calendarCells = [];

        for ($index = 1; $index < $monthStart->dayOfWeekIso; $index++) {
            $calendarCells[] = null;
        }

        $cursor = $monthStart->copy();

        while ($cursor->lte($monthEnd)) {
            $dayKey = $cursor->toDateString();
            $daySchedules = $schedulesByDay->get($cursor->format('l'), collect());
            $dayAppointments = $appointmentsByDate->get($dayKey, collect());

            $hasSchedule = $daySchedules->isNotEmpty();
            $appointmentsCount = $dayAppointments->count();
            $state = $hasSchedule
                ? ($appointmentsCount > 0 ? 'occupied' : 'available')
                : 'unavailable';

            $calendarCells[] = [
                'day' => $cursor->day,
                'date' => $dayKey,
                'state' => $state,
                'schedules_count' => $daySchedules->count(),
                'appointments_count' => $appointmentsCount,
            ];

            $cursor->addDay();
        }

        while (count($calendarCells) % 7 !== 0) {
            $calendarCells[] = null;
        }

        $this->calendarCells = $calendarCells;
    }

    private function resolveInitialDoctorId(): ?int
    {
        if ($this->doctorLocked) {
            return $this->currentUser()->doctor?->id;
        }

        $firstDoctorId = array_key_first($this->doctorOptions);

        return $firstDoctorId !== null ? (int) $firstDoctorId : null;
    }

    /**
     * @return array<int, string>
     */
    private function buildDoctorOptions(): array
    {
        $user = $this->currentUser();

        if ($this->doctorLocked) {
            $doctor = $user->doctor?->loadMissing('user:id,name');

            if (! $doctor) {
                return [];
            }

            return [
                $doctor->id => $this->formatDoctorLabel($doctor),
            ];
        }

        return Doctor::query()
            ->with('user:id,name')
            ->orderBy('id')
            ->get()
            ->mapWithKeys(
                fn (Doctor $doctor): array => [
                    $doctor->id => $this->formatDoctorLabel($doctor),
                ],
            )
            ->all();
    }

    private function formatDoctorLabel(Doctor $doctor): string
    {
        $name = $doctor->user?->name ?? "Doctor #{$doctor->id}";

        return "{$name} - {$doctor->specialty}";
    }

    private function isDoctorScoped(): bool
    {
        $user = $this->currentUser();

        return $user->hasPermissionTo('read_appointments') && ! $user->hasAnyPermission(['read_all_appointments', 'manage_appointments']);
    }

    private function currentUser(): User
    {
        /** @var User $user */
        $user = Filament::auth()->user();

        return $user;
    }
}
