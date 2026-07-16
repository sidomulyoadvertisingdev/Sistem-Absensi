<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Schedule
|--------------------------------------------------------------------------
|
| Backup foto absen ke Google Drive tiap jam 1 malam.
|
*/
Schedule::command('absensi:backup-foto')->dailyAt('01:00');

/*
|--------------------------------------------------------------------------
| Cleanup foto lama dari server tanggal 1 tiap bulan jam 2 pagi.
|
*/
Schedule::command('absensi:cleanup-old-foto')->cron('0 2 1 * *');
