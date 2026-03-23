<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Schedule;
use Carbon\Carbon;

class AppointmentValidationService
{
    /**
     * @param  array{
     *     doctor_id: int|string,
     *     appointment_date: string,
     *     appointment_start_time: string,
     *     appointment_end_time: string
     * }  $payload
     */
    public function isWithinDoctorSchedule(array $payload): bool
    {
        try {
            $dayOfWeek = Carbon::parse($payload['appointment_date'])->format('l');
        } catch (\Throwable) {
            return false;
        }

        $startTime = $this->normalizeTime($payload['appointment_start_time']);
        $endTime = $this->normalizeTime($payload['appointment_end_time']);

        if (! $startTime || ! $endTime) {
            return false;
        }

        return Schedule::query()
            ->where('doctor_id', $payload['doctor_id'])
            ->where('day_of_week', $dayOfWeek)
            ->where('start_time', '<=', $startTime)
            ->where('end_time', '>=', $endTime)
            ->exists();
    }

    /**
     * @param  array{
     *     doctor_id: int|string,
     *     appointment_date: string,
     *     appointment_start_time: string,
     *     appointment_end_time: string
     * }  $payload
     */
    public function hasConflict(array $payload, ?Appointment $ignore = null): bool
    {
        $startTime = $this->normalizeTime($payload['appointment_start_time']);
        $endTime = $this->normalizeTime($payload['appointment_end_time']);

        if (! $startTime || ! $endTime) {
            return false;
        }

        return Appointment::query()
            ->where('doctor_id', $payload['doctor_id'])
            ->whereDate('appointment_date', $payload['appointment_date'])
            ->where(function ($query): void {
                $query
                    ->whereNull('status')
                    ->orWhere('status', '!=', 'canceled');
            })
            ->when(
                $ignore,
                fn ($query) => $query->whereKeyNot($ignore->getKey()),
            )
            ->where(function ($query) use ($startTime, $endTime): void {
                $query
                    ->where('appointment_start_time', '<', $endTime)
                    ->where('appointment_end_time', '>', $startTime);
            })
            ->exists();
    }

    public function normalizeTime(?string $time): ?string
    {
        if (! is_string($time) || $time === '') {
            return null;
        }

        foreach (['H:i:s', 'H:i'] as $format) {
            try {
                return Carbon::createFromFormat($format, $time)->format('H:i:s');
            } catch (\Throwable) {
                // Keep trying supported formats.
            }
        }

        return null;
    }
}
