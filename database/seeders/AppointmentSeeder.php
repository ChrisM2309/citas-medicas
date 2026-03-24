<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class AppointmentSeeder extends Seeder
{
    /**
     * @var array<string, int>
     */
    private const DAY_INDEX = [
        'Monday' => Carbon::MONDAY,
        'Tuesday' => Carbon::TUESDAY,
        'Wednesday' => Carbon::WEDNESDAY,
        'Thursday' => Carbon::THURSDAY,
        'Friday' => Carbon::FRIDAY,
        'Saturday' => Carbon::SATURDAY,
        'Sunday' => Carbon::SUNDAY,
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $doctors = Doctor::query()
            ->with(['user:id,email', 'schedules'])
            ->get();

        $patientIds = Patient::query()->pluck('id');

        if ($doctors->isEmpty() || $patientIds->isEmpty()) {
            return;
        }

        $usedSlots = [];

        foreach ($doctors as $doctor) {
            if ($doctor->schedules->isEmpty()) {
                continue;
            }

            $isDemoDoctor = $doctor->user?->email === 'doctor@correo.com';
            $scheduledCount = $isDemoDoctor ? 8 : fake()->numberBetween(3, 5);
            $completedCount = $isDemoDoctor ? 3 : fake()->numberBetween(1, 2);
            $canceledCount = $isDemoDoctor ? 1 : fake()->numberBetween(0, 1);

            for ($i = 0; $i < $scheduledCount; $i++) {
                $this->seedAppointmentForDoctor(
                    doctor: $doctor,
                    schedules: $doctor->schedules,
                    patientIds: $patientIds,
                    status: 'scheduled',
                    future: true,
                    usedSlots: $usedSlots,
                );
            }

            for ($i = 0; $i < $completedCount; $i++) {
                $this->seedAppointmentForDoctor(
                    doctor: $doctor,
                    schedules: $doctor->schedules,
                    patientIds: $patientIds,
                    status: 'completed',
                    future: false,
                    usedSlots: $usedSlots,
                );
            }

            for ($i = 0; $i < $canceledCount; $i++) {
                $this->seedAppointmentForDoctor(
                    doctor: $doctor,
                    schedules: $doctor->schedules,
                    patientIds: $patientIds,
                    status: 'canceled',
                    future: true,
                    usedSlots: $usedSlots,
                );
            }
        }
    }

    /**
     * @param Collection<int, \App\Models\Schedule> $schedules
     * @param Collection<int, int> $patientIds
     * @param array<string, bool> $usedSlots
     */
    private function seedAppointmentForDoctor(
        Doctor $doctor,
        Collection $schedules,
        Collection $patientIds,
        string $status,
        bool $future,
        array &$usedSlots,
    ): void {
        for ($attempt = 0; $attempt < 40; $attempt++) {
            $schedule = $schedules->random();
            $date = $this->randomDateForDay($schedule->day_of_week, $future);

            if (! $date) {
                continue;
            }

            [$startTime, $endTime] = $this->randomSlotWithinSchedule($schedule->start_time, $schedule->end_time);

            if (! $startTime || ! $endTime) {
                continue;
            }

            $slotKey = "{$doctor->id}|{$date}|{$startTime}";

            if (isset($usedSlots[$slotKey])) {
                continue;
            }

            $usedSlots[$slotKey] = true;

            Appointment::query()->create([
                'doctor_id' => $doctor->id,
                'patient_id' => $patientIds->random(),
                'appointment_date' => $date,
                'appointment_start_time' => $startTime,
                'appointment_end_time' => $endTime,
                'status' => $status,
                'reason' => fake()->sentence(),
            ]);

            return;
        }
    }

    private function randomDateForDay(string $dayOfWeek, bool $future): ?string
    {
        $dayIndex = self::DAY_INDEX[$dayOfWeek] ?? null;

        if ($dayIndex === null) {
            return null;
        }

        $start = $future
            ? now()->startOfDay()->addDay()
            : now()->startOfDay()->subWeeks(8);
        $end = $future
            ? now()->startOfDay()->addMonths(3)
            : now()->startOfDay()->subDay();

        $dates = [];
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            if ($cursor->dayOfWeek === $dayIndex) {
                $dates[] = $cursor->toDateString();
            }

            $cursor->addDay();
        }

        if ($dates === []) {
            return null;
        }

        return fake()->randomElement($dates);
    }

    /**
     * @return array{0: string|null, 1: string|null}
     */
    private function randomSlotWithinSchedule(string $startTime, string $endTime): array
    {
        $start = Carbon::createFromFormat('H:i:s', $startTime);
        $end = Carbon::createFromFormat('H:i:s', $endTime);
        $latestStart = $end->copy()->subHour();

        if ($latestStart->lt($start)) {
            return [null, null];
        }

        $hour = fake()->numberBetween($start->hour, $latestStart->hour);
        $appointmentStart = sprintf('%02d:00:00', $hour);
        $appointmentEnd = sprintf('%02d:00:00', $hour + 1);

        return [$appointmentStart, $appointmentEnd];
    }
}
