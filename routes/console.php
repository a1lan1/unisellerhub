<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

// Telescope
Schedule::command('telescope:prune')->everyTwoHours();

// Backup
Schedule::command('backup:run')->daily()->at('08:00');
Schedule::command('backup:clean')->daily()->at('20:00');

Schedule::command('storage:clear')->environments(['local'])->everySixHours();

// Mock Activity Simulation (Webhooks)
Schedule::command('mock:simulate-activity')->environments(['local'])->everyThirtySeconds();
