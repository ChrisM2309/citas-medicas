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
use UnitEnum;

class DoctorAvailabilityCalendar extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::CalendarDays;

    protected static ?string $navigationLabel = 'Calendario';

    protected static ?string $title = 'Calendario de disponibilidad médica';

    protected static string|UnitEnum|null $navigationGroup = 'Agenda';

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

        if ($user->hasRole('DOCTOR')) {
            return true;
        }

        return $user->hasAnyPermission(['read_appointments', 'read_all_appointments', 'manage_appointments', 'manage_users']);
    }

    public function goToPreviousMonth(): void
    {
        $this->month = $this->resolveMonthStart()
            ->subMonth()
            ->format('Y-m');

        $this->refreshCalendar();
    }

    public function goToNextMonth(): void
    {
        $this->month = $this->resolveMonthStart()
            ->addMonth()
            ->format('Y-m');

        $this->refreshCalendar();
    }

    public function updateDoctor(string $doctorId): void
    {
        $this->doctorId = is_numeric($doctorId) ? (int) $doctorId : null;

        $this->refreshCalendar();
    }

    public function updateMonth(string $value): void
    {
        $this->month = $this->normalizeMonth($value);

        $this->refreshCalendar();
    }

    public function getMonthLabelProperty(): string
    {
        return $this->resolveMonthStart()
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

        $monthStart = $this->resolveMonthStart();
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
            ->selectRaw('day_of_week, COUNT(*) as total')
            ->groupBy('day_of_week')
            ->pluck('total', 'day_of_week');

        $appointmentsByDate = Appointment::query()
            ->selectRaw('appointment_date, COUNT(*) as total')
            ->where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', '>=', $monthStart->toDateString())
            ->whereDate('appointment_date', '<=', $monthEnd->toDateString())
            ->where('status', '!=', 'canceled')
            ->groupBy('appointment_date')
            ->pluck('total', 'appointment_date');

        $calendarCells = [];

        for ($index = 1; $index < $monthStart->dayOfWeekIso; $index++) {
            $calendarCells[] = null;
        }

        $cursor = $monthStart->copy();

        while ($cursor->lte($monthEnd)) {
            $dayKey = $cursor->toDateString();
            $schedulesCount = (int) ($schedulesByDay->get($cursor->format('l')) ?? 0);
            $appointmentsCount = (int) ($appointmentsByDate->get($dayKey) ?? 0);

            $hasSchedule = $schedulesCount > 0;
            $state = $hasSchedule
                ? ($appointmentsCount > 0 ? 'occupied' : 'available')
                : 'unavailable';

            $calendarCells[] = [
                'day' => $cursor->day,
                'date' => $dayKey,
                'state' => $state,
                'schedules_count' => $schedulesCount,
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

        if ($user->hasRole('DOCTOR')) {
            return true;
        }

        return $user->hasPermissionTo('read_appointments') && ! $user->hasAnyPermission(['read_all_appointments', 'manage_appointments']);
    }

    private function normalizeMonth(string $value): string
    {
        if (preg_match('/^\d{4}-\d{2}$/', $value)) {
            return $value;
        }

        return now()->startOfMonth()->format('Y-m');
    }

    private function resolveMonthStart(): Carbon
    {
        $this->month = $this->normalizeMonth($this->month);

        return Carbon::createFromFormat('Y-m', $this->month)->startOfMonth();
    }

    private function currentUser(): User
    {
        /** @var User $user */
        $user = Filament::auth()->user();

        return $user;
    }
}
