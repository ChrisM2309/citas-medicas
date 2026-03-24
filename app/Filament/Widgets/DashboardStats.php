<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use App\Models\Patient;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class DashboardStats extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $today = now('America/El_Salvador')->toDateString();

        $todayAppointmentsCount = DB::table('appointments')
            ->where('appointment_date', $today)
            ->whereIn('status', ['scheduled', 'completed'])
            ->count();

        return [
            Stat::make('Pacientes', Patient::count())
                ->description('Total registrados')
                ->color('primary'),

            Stat::make('Citas de hoy', $todayAppointmentsCount)
                ->description('Pendientes y completadas')
                ->color('success'),
        ];
    }
}