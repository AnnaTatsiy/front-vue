<?php

namespace App\Console;

use App\Http\Controllers\Admin\GroupWorkoutController;
use App\Http\Controllers\Admin\SignUpPersonalWorkoutController;
use App\Http\Helpers\Utils;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{

    protected function schedule(Schedule $schedule): void
    {

        $schedule->call(function (){
            GroupWorkoutController::preparationEdit(); // через каждый час
        })->timezone('Europe/Moscow')->everyFiveMinutes();//->dailyAt('08:00'); // ежедневно в 15:00

        $schedule->call(function (){
            GroupWorkoutController:: preparationAdd();
        })->timezone('Europe/Moscow')->everyFiveMinutes();//->dailyAt('08:00'); // ежедневно в 09:00

        $schedule->call(function (){
            SignUpPersonalWorkoutController:: addSignUpPersonalWorkoutsForAllCoaches();
        })->timezone('Europe/Moscow')->everyFiveMinutes();//->dailyAt('09:00'); // ежедневно в 09:00

        // имитация работы фитнес клуба

        // клиенты оформляют абонементы безлимитные и абонементы на персональные тренировки
        $schedule->call(function (){
            Utils::subscriptionsSeeder();
        })->timezone('Europe/Moscow')->everyFiveMinutes();//->dailyAt('10:00'); // ежедневно в 10:00


        // делаем записи на групповые тренировки каждого клиента
        $schedule->call(function (){
            Utils::signUpGroupWorkoutSeeder();// ->everyFiveMinutes()
        })->timezone('Europe/Moscow')->everyFiveMinutes();//->dailyAt('15:00'); // ежедневно в 15:00

        // делаем записи на персональные тренировки каждого клиента
        $schedule->call(function (){
            Utils::signUpPersonalWorkoutsSeeder();
        })->timezone('Europe/Moscow')->everyFiveMinutes();//->dailyAt('15:00'); // ежедневно в 15:00
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
