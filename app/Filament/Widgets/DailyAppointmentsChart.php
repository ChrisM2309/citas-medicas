<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use Filament\Widgets\ChartWidget;

class DailyAppointmentsChart extends ChartWidget
{
    protected ?string $heading = 'Citas diarias';

    protected static ?int $sort = 2;

    public ?string $filter = '7_days';

    protected function getFilters(): ?array
    {
        return [
            '7_days' => 'Últimos 7 días',
            '30_days' => 'Últimos 30 días',
        ];
    }

    protected function getData(): array
    {
        $days = match ($this->filter) {
            '30_days' => 30,
            default => 7,
        };

        $startDate = now('America/El_Salvador')->subDays($days - 1)->startOfDay();
        $endDate = now('America/El_Salvador')->endOfDay();

        $appointmentsByDay = Appointment::query()
            ->whereBetween('appointment_date', [
                $startDate->toDateString(),
                $endDate->toDateString(),
            ])
            ->whereIn('status', ['scheduled', 'completed'])
            ->selectRaw('appointment_date, COUNT(*) as total')
            ->groupBy('appointment_date')
            ->orderBy('appointment_date')
            ->pluck('total', 'appointment_date');

        $labels = [];
        $data = [];

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $formattedDate = $date->toDateString();

            $labels[] = $date->format('d/m');
            $data[] = $appointmentsByDay[$formattedDate] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Citas',
                    'data' => $data,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}