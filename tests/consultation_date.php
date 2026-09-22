<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Appointment;
use App\Models\AppointmentSlot;
use Illuminate\Support\Carbon;

try {
    Carbon::setTestNow(Carbon::parse('2026-09-10 23:59:59', config('app.timezone')));
    foreach ([
        ['2026-09-09 23:59:59', false],
        ['2026-09-10 00:00:00', true],
        ['2026-09-10 09:15:00', true],
        ['2026-09-11 09:15:00', true],
        [null, false],
    ] as [$date, $expected]) {
        $appointment = new Appointment();
        $appointment->setRelation('slot', new AppointmentSlot(['slot_start' => $date]));
        if ($appointment->isWithinConsultationDate() !== $expected) {
            throw new RuntimeException('Unexpected consultation eligibility for '.($date ?? 'missing date'));
        }
    }
    $appointment->setRelation('slot', null);
    if ($appointment->isWithinConsultationDate()) {
        throw new RuntimeException('A missing slot must block consultation.');
    }
    $appointment->setRelation('slot', new AppointmentSlot(['slot_start' => '2026-09-10 09:15:00']));
    Carbon::setTestNow(Carbon::parse('2026-09-11 00:00:00', config('app.timezone')));
    if ($appointment->isWithinConsultationDate()) {
        throw new RuntimeException('Consultation must close at midnight after the appointment date.');
    }
    echo "Consultation date checks passed.\n";
} finally {
    Carbon::setTestNow();
}
