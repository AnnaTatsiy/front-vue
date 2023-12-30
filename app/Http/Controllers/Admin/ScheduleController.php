<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Utils;
use App\Models\Coach;
use App\Models\Schedule;
use App\Models\User;
use App\Notifications\userNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Notification;

class ScheduleController extends Controller
{
    // вывести расписание групповых тренировок
    // id - день недели
    public function schedules($id): JsonResponse{
        return response()->json(Schedule::with('day', 'gym', 'coach', 'workout_type')->where('day_id','=', $id)->get());
    }

    // вывести расписание групповых тренировок
    public function schedulesGetAll(): JsonResponse{
        return response()->json(Schedule::with('day', 'gym', 'coach', 'workout_type')->get());
    }

    // удалить запись расписания
    public function deleteSchedule(Request $request): JsonResponse{
        $response = [];

        $response["status"] = "failed";
        $response['answer'] = null;
        $response["message"] = "Ошибка при удалении тренировки";

        $id = +$request->input('id');

        $workout = Schedule::all()->where('id', $id)->first();

        if($workout !== null){
            $workout->delete();

            //уведомляем всех клинтов о изменении расписания
            $users = User::all()->where('role', 'customer');

            Notification::send($users, new userNotification('Расписание групповых тренировок было изменено.'));

            // уведомляем тренера об удалении тренировки, которую он ведет
            $coach = $workout->coach->user;
            $time = substr($workout->time_begin,0,-3);

            $message = "Из расписания была удалена ваша групповая тренировка: {$workout->day->title}, начало в {$time},
            {$workout->gym->title}, {$workout->workout_type->title}.";

            Notification::send($coach, new userNotification($message));

            $response["status"] = "success";
            $response["message"] = "Удаление тренировки успешно завершено!";
            $response['answer'] = $workout;
        }

        return response()->json($response);
    }

    // добавить/редактировать тренировку в расписание
    // валидация:
    // - тренировки проходят с 8:00 по 20:00
    // - максимальная длительность тренировки 1 час 30 минут
    // - минимальная длительность тренировки 30 минут
    // - в одном зале проходит одна тренировка
    // - одну тренировку ведет один тренер
    public function decorSchedule(Request $request): JsonResponse
    {
        $response = [];

        $isAdd = $request->input('isAdd'); //признак добавления
        $day = $request->input('day');
        $gym = $request->input('gym');
        $time_begin = $request->input('time_begin');
        $time_end =  $request->input('time_end');
        $coach = $request->input('coach');
        $workout_type = $request->input('workout_type');

        $flag = true;

        $response["status"] = "";
        $response['answer'] = null;
        $response["message"] = ($isAdd) ? "Тренировка не была добавлена" : "Тренировка не была редактирована";

        $response["errors"] = array(
            'coach' => "",
            'workouts_coach' => array(),
            'workouts_gym' => array(),
            'time_begin' => "",
            'time_end' => ""
        );

        //поиск тренера
        $coach = Coach::all()->where('passport', $coach)->first();
        if($coach === null){
            $response["status"] = "failed";
            $response["errors"]['coach'] = "Тренер с данным номером-серии паспорта не найден";
        }else{

            // одну тренировку ведет один тренер
            $workouts_coach = ($isAdd) ? Schedule::all()->where('day_id', $day)->where('coach_id', $coach->id)
                                        :Schedule::all()->where('day_id', $day)->where('coach_id', $coach->id)
                                                        ->where('id', '!=' ,$request->input('id'));
            foreach ($workouts_coach as $item){
                if(($time_begin < $item->time_begin &&  $time_end > $item->time_begin)
                    || ($time_begin > $item->time_begin && $time_end < $item->time_end)
                    || ($time_begin < $item->time_end && $time_end > $item->time_end)){
                    $response["errors"]['workouts_coach'][] = date( 'H:i', strtotime($item->time_begin))  . " - " . date( 'H:i', strtotime($item->time_end));
                }
            }
        }

        if($time_end < $time_begin){
            $response["status"] = "failed";
            $response["errors"]['time_begin'] = "Время начала тренировки больше времени конца";
            $response["errors"]['time_end'] = "Время начала тренировки больше времени конца";
        }

        // тренировки проходят с 8:00
        if($time_begin < '08:00') {
            $response["status"] = "failed";
            $response["errors"]['time_begin'] = "Тренировки проходят с 8:00";
        }

        // тренировки проходят до 20:00
        if($time_end > '20:00') {
            $response["status"] = "failed";
            $response["errors"]['time_end'] = "Тренировки проходят до 20:00";
        }

        // максимальная длительность тренировки 1 час 30 минут
        // минимальная длительность тренировки 30 минут
        $date1 = Carbon::parse(date('G:i',strtotime($time_begin)));
        $date2 = Carbon::parse(date('G:i',strtotime($time_end)));

        $totalDuration = $date1->diffInSeconds($date2);

        if($totalDuration > 5400){
            $response["status"] = "failed";
            $response["errors"]['time_begin'] = "Максимальная длительность тренировки 1 час 30 минут";
            $response["errors"]['time_end'] = "Максимальная длительность тренировки 1 час 30 минут";
        }

        if($totalDuration < 1800){
            $response["status"] = "failed";
            $response["errors"]['time_begin'] = "Минимальная длительность тренировки 30 минут";
            $response["errors"]['time_end'] = "Минимальная длительность тренировки 30 минут";
        }

        // в одном зале проходит одна тренировка
        $workouts_gym = ($isAdd) ? Schedule::all()->where('day_id', $day)->where('gym_id', $gym)
                                 : Schedule::all()->where('day_id', $day)->where('gym_id', $gym)
                                                  ->where('id', '!=' ,$request->input('id'));
        foreach ($workouts_gym as $item){
            if(($time_begin < $item->time_begin &&  $time_end > $item->time_begin)
            || ($time_begin > $item->time_begin && $time_end < $item->time_end)
            || ($time_begin < $item->time_end && $time_end > $item->time_end)){
                $response["errors"]['workouts_gym'][] = date( 'H:i', strtotime($item->time_begin))  . " - " . date( 'H:i', strtotime($item->time_end));
            }
        }

        if(count($response["errors"]['workouts_gym']) !== 0){
            $flag = false;
            $response["status"] = "failed";
            $error = "В этом же зале проходят тренировки на ";
            foreach ($response["errors"]['workouts_gym'] as $workout){
                $error .= $workout . ', ';
            }

            $error = Utils::strLeftReplace(', ', "", $error);
            $response["errors"]['workouts_gym'] = $error;
        }

        if(count($response["errors"]['workouts_coach']) !== 0){
            $flag = false;
            $response["status"] = "failed";
            $error = "Этот же тренер проходит тренировки на ";
            foreach ($response["errors"]['workouts_coach'] as $workout){
                $error .= $workout . ', ';
            }

            $error = Utils::strLeftReplace(', ', "", $error);
            $response["errors"]['workouts_coach'] = $error;
        }

        if($flag){
            $response["errors"]['workouts_gym'] = '';
            $response["errors"]['workouts_coach'] = '';
        }

        //валидация пройдена - добавляем запись
        if($response["status"] !== "failed"){

            $workout = ($isAdd) ? new Schedule() : Schedule::all()->where('id', $request->input('id'))->first();
            $workout->day_id = $day;
            $workout->gym_id = $gym;
            $workout->time_begin = $time_begin;
            $workout->time_end = $time_end;
            $workout->coach_id = $coach->id;
            $workout->workout_type_id = $workout_type;

            $workout->save();

            //уведомляем всех клинтов о изменении расписания
            $users = User::all()->where('role', 'customer');

            Notification::send($users, new userNotification('Расписание групповых тренировок было изменено.'));

            // уведомляем тренера об добавление/редактирование тренировки
            $s = ($isAdd) ? 'добавлена':'редактирована';
            $time = ($isAdd) ? $workout->time_begin : substr($workout->time_begin,0,-3);

            $message = "В расписании была {$s} ваша групповая тренировка: {$workout->day->title}, начало в {$time},
            {$workout->gym->title}, {$workout->workout_type->title}.";

            Notification::send($workout->coach->user, new userNotification($message));

            $response["status"] = "success";
            $response['answer'] = Schedule::with('day', 'gym', 'coach', 'workout_type')->where('id', $workout->id)->get();
            $response["message"] = ($isAdd) ? "Тренировка была добавлена успешно!" : "Тренировка была редактирована успешно!" ;
        }

        return response()->json($response);
    }
}
