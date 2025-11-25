<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\ServiceDocument;
use App\Notifications\DocumentExpiringSoon;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->call(function () {
            $todayPlus30 = now()->addDays(30)->toDateString();
            $todayPlus7 = now()->addDays(7)->toDateString();

            ServiceDocument::with('company.user')
                ->whereNotNull('expires_at')
                ->where('status', 'verified')
                ->get()
                ->each(function ($doc) use ($todayPlus30, $todayPlus7) {
                    $date = $doc->expires_at?->toDateString();
                    $user = $doc->company?->user;
                    if (!$user) return;

                    if ($date === $todayPlus30) {
                        $user->notify(new DocumentExpiringSoon($doc, 30));
                    }
                    if ($date === $todayPlus7) {
                        $user->notify(new DocumentExpiringSoon($doc, 7));
                    }
                });
        })->dailyAt('09:00');
    }

    protected function commands(): void
    {
        // Load commands if needed
    }
}