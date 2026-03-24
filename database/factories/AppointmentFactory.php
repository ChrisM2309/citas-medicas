<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
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
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $doctor = Doctor::query()
            ->with('schedules')
            ->inRandomOrder()
            ->first();

        if (! $doctor || $doctor->schedules->isEmpty()) {
            $startHour = fake()->numberBetween(8, 16);

            return [
                'patient_id' => Patient::factory(),
                'doctor_id' => Doctor::factory(),
                'appointment_date' => fake()->dateTimeBetween('now', '+3 months')->format('Y-m-d'),
                'appointment_start_time' => sprintf('%02d:00:00', $startHour),
                'appointment_end_time' => sprintf('%02d:00:00', min($startHour + 1, 23)),
                'status' => fake()->randomElement(['scheduled', 'completed', 'canceled']),
                'reason' => fake()->sentence(),
            ];
        }

        $schedule = $doctor->schedules->random();
        $date = $this->randomFutureDateForDay($schedule->day_of_week) ?? fake()->dateTimeBetween('now', '+3 months')->format('Y-m-d');
        [$startTime, $endTime] = $this->randomSlotWithinSchedule($schedule->start_time, $schedule->end_time);

        $startTime ??= '08:00:00';
        $endTime ??= '09:00:00';

        return [
            'patient_id' => Patient::factory(),
            'doctor_id' => $doctor->id,
            'appointment_date' => $date,
            'appointment_start_time' => $startTime,
            'appointment_end_time' => $endTime,
            'status' => fake()->randomElement(['scheduled', 'completed', 'canceled']),
            'reason' => fake()->sentence(),
        ];
    }

    private function randomFutureDateForDay(string $dayOfWeek): ?string
    {
        $dayIndex = self::DAY_INDEX[$dayOfWeek] ?? null;

        if ($dayIndex === null) {
            return null;
        }

        $dates = [];
        $cursor = now()->startOfDay()->addDay();
        $end = now()->startOfDay()->addMonths(3);

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

        return [
            sprintf('%02d:00:00', $hour),
            sprintf('%02d:00:00', $hour + 1),
        ];
    }
}
