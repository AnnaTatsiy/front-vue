<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Admin\SignUpPersonalWorkoutController;
use App\Http\Controllers\Controller;
use App\Http\Helpers\Utils;
use App\Models\Coach;
use App\Models\LimitedPriceList;
use App\Models\PersonalSchedule;
use App\Models\SignUpPersonalWorkout;
use App\Models\TimeEditPersonalSchedule;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CoachController extends Controller
{
    //получить тренера из авторизированного пользователя
    private function getCoach()
    {
        $user = User::with('coach')->where('email', auth()->user()->email)->first();
        return Coach::with('user')->where('user_id', $user->id)->first();
    }

    //получить тренера из авторизированного пользователя JSON
    public function getCoachJSON(): JsonResponse
    {
        return response()->json($this->getCoach());
    }

    //может изменить цену на абонемент (упрощение модели)
    public function editLimitedPrice(Request $request): JsonResponse
    {
        $response = [];

        $lo = $request->input('lo');
        $hi = $request->input('hi');

        $validator = Validator::make($request->all(),
            [
                'lo' => "bail|required|numeric",
                'hi' => "bail|required|numeric"
            ],
            [
                'lo.required' => "Стоимость не была указана",
                'hi.required' => "Стоимость не была указана",
                'lo.numeric' => "Введите обе стоимости",
                'hi.numeric' => "Введите обе стоимости"
            ]
        );

        if ($validator->fails()) {
            return response()->json(["status" => "failed", "message" => "Validation error", "errors" => $validator->errors()->messages()[$validator->errors()->keys()[0]][0]]);
        }

        $validator = Validator::make($request->all(),
            [
                'lo' => "bail|required|numeric|min:1000|max:{$hi}",
                'hi' => "bail|required|numeric|min:{$lo}|max:20000"
            ],
            [
                'lo.min' => "Минимальная стоимость абонемента на 8 посещений 1000",
                'hi.min' => "Минимальная стоимость абонемента на 12 посещений ${lo}",
                'lo.max' => "Максимальная стоимость абонемента на 8 посещений ${hi}",
                'hi.max' => "Максимальная стоимость абонемента на 12 посещений 20000"
            ]
        );

        if ($validator->fails()) {
            return response()->json(["status" => "failed", "message" => "Validation error", "errors" => $validator->errors()->messages()[$validator->errors()->keys()[0]][0]]);
        }

        if ($request->has('lo') && $request->has('hi')) {

            $coach = $this->getCoach();

            //находим прайс абонемента на 8 и 12 посещений
            $price_lo = LimitedPriceList::all()->where('coach_id', $coach->id)->where('amount_workout', 8)->first();
            $price_hi = LimitedPriceList::all()->where('coach_id', $coach->id)->where('amount_workout', 12)->first();

            //изменить цену абенемента на 8 посещений
            $price_lo->price = $lo;
            $price_lo->save();

            //изменить цену абенемента на 12 посещений
            $price_hi->price = $hi;
            $price_hi->save();

            $response["status"] = "success";
            $response["message"] = "Изменения успешно внесены!";

        } else {

            $response["status"] = "failed";
            $response["message"] = "Failed!";
        }
        return response()->json($response);

    }

    //получить признак доступна ли продажа абонементов
    public function getSale(): JsonResponse
    {
        $coach = $this->getCoach();

        return response()->json(
            ["sale" => (bool)$coach->sale]
        );
    }

    // тренер может запретить продажу абонементов
    public function changeSale(): JsonResponse
    {
        $coach = $this->getCoach();
        $sale = (bool)$coach->sale;

        $coach->sale = !$sale;
        $coach->save();

        return response()->json(
            ["sale" => (bool)$coach->sale]
        );
    }

    //получить дату последнего изменения расписания
    public function getDateOfChange(): JsonResponse
    {
        $coach = $this->getCoach();
        $date = TimeEditPersonalSchedule::all()->where('coach_id', $coach->id)->max('date_edit');

        return response()->json(
            ["date" => $date]
        );
    }

    //получить расписание авторизированного тренера
    public function getSchedule(): JsonResponse
    {
        $coach = $this->getCoach();

        $schedule = PersonalSchedule::with("day")->where('coach_id', $coach->id)->get();

        return response()->json($schedule);
    }

    //получить записи на персональные тренировки для авторизированного тренера
    public function getSignUpPersonalWorkoutsByAuthCoach($page): JsonResponse
    {
        $coach = $this->getCoach();
        return response()->json(SignUpPersonalWorkoutController::getSignUpPersonalWorkoutsByCoach($coach->id, $page));
    }

    //сколько тренировок нужно выставить авторизированному тренеру в расписании(за неделю) и сколько он выставил
    public function getRequiredAmountWorkouts(): JsonResponse
    {
        $coach = $this->getCoach();
        return response()->json(\App\Http\Controllers\Admin\CoachController::requiredAmountWorkouts($coach->id));
    }

    // CRUD

    // Добавление тренировки в расписание
    // Валидация по времени:
    // - тренировка длится 1 час 30 минут
    // - тренировки не могут пересекаться по времени
    // - тренер может работать с 6:00 до 22:00
    // Тренер может добавлять тренировки всегда
    public function addWorkout(Request $request): JsonResponse
    {

        $response = [];

        $response["status"] = "";
        $response['answer'] = null;
        $response["message"] = "Тренировка не была добавлена";
        $response["errors"] = array(
            'workouts' => '',
            'time' => ''
        );

        $coach = $this->getCoach();

        $day = $request->input('day');
        $time = $request->input('time');

        $workouts = PersonalSchedule::with('day')->where('coach_id', $coach->id)
            ->where('day_id', $day)->orderBy('time_begin')->get();

        $error_workouts = array();

        // валидация
        foreach ($workouts as $workout) {
            if (!((Utils::incTime($time) <= $workout->time_begin) || ($time >= Utils::incTime($workout->time_begin)))) {
                $error_workouts[] = $workout->time_begin;
            }
        }

        if ($time < '06:00') {
            $response["errors"]['time'] = "Индивидуальные тренировки не могут начинаться раньше 6:00";
            $response["status"] = "failed";
        }

        if (Utils::incTime($time) > '22:00' || $time >= '22:00') {
            $response["errors"]['time'] = "Индивидуальные тренировки должны быть окончены до 22:00";
            $response["status"] = "failed";
        }

        if (count($error_workouts) !== 0) {

            $error = (count($error_workouts) === 1) ?
                "Тренировка пересекается с тренировкой на " . date('H:i', strtotime($error_workouts[0]))
                : "Тренировка пересекается с тренировками на " . date('H:i', strtotime($error_workouts[0])) . ' и ' . date('H:i', strtotime($error_workouts[1]));

            $response["errors"]['workouts'] = $error;
            $response["status"] = "failed";
        }

        if ($response["status"] !== "failed") {

            //валидация пройдена - добавляем запись
            $workout = new PersonalSchedule();
            $workout->day_id = $day;
            $workout->time_begin = $time;
            $workout->coach_id = $coach->id;

            $workout->save();

            $response["status"] = "success";
            $response['answer'] = $workout;
            $response["message"] = "Тренировка была добавлена успешно!";

            // при добавлении тренировки в расписание, добавляем места для записи клиентов

            // узнаем даты
            $date = date("w") == 0 ? 7 : date("w");

            if($date == $day){
                $nextDayOfWeek = date("Y-m-d", strtotime("+7 days", strtotime(date('Y-m-d'))));
                $nextNextDayOfWeek = date("Y-m-d", strtotime("+7 days", strtotime($nextDayOfWeek)));
            } else {
                $nextDay = strtotime('next ' . Utils::$getDayOfWeekById[$day - 1]);
                $nextDayOfWeek = date("Y-m-d", $nextDay);
                $nextNextDayOfWeek = date("Y-m-d", strtotime("+7 days", $nextDay));
            }

            // делаем записи
            SignUpPersonalWorkout::create([
                'date_begin' => $nextDayOfWeek,
                'customer_id' => null,
                'schedule_id' => $workout->id
            ]);

            SignUpPersonalWorkout::create([
                'date_begin' => $nextNextDayOfWeek,
                'customer_id' => null,
                'schedule_id' => $workout->id
            ]);
        }

        return response()->json($response);
    }

    // Изменяем расписание транзакцией так как это доступно только раз в 2 недели
    // Удаление тренировок из расписания

    // получить расписание для редактирования
    // валидация
    // изменять расписание возможно 1 раз в 14 дней - проверяем дату последнего изменения
    public function getScheduleForEdit(): JsonResponse
    {

        $response = [];

        $response["status"] = "";
        $response['answer'] = null;
        $response["error"] = "";

        //получаем дату последнего изменения
        $date = $this->getDateOfChange()->original['date'];

        // валидация
        if ($date !== null) {

            $value = date("Y-m-d", strtotime("+14 day", strtotime($date)));

            if ($value >= date("Y-m-d")) {
                $response["status"] = "failed";
                $response["error"] = "C момента последнего редактирования прошло менее 2 недель.
                Дата последнего редактирования " . date("d.m.Y", strtotime($date));
            }
        }

        // валидация пройдена
        if ($response["status"] !== "failed") {
            $response["status"] = "success";
            $response['answer'] = $this->getSchedule()->original;
        }

        return response()->json($response);
    }

    // редактирование расписания
    // валидация - должно быть необходимое количество тренировок
    public function editSchedule(Request $request): JsonResponse
    {
        $coach = $this->getCoach();

        // получаем новое расписание
        $data = $request->input('data');

        // нельзя редактировать расписание если не хватает тренировок
        // узнаем сколько тренировок необходимо
        $required = \App\Http\Controllers\Admin\CoachController::requiredAmountWorkouts($coach->id)->original['required'];

        if(count($data) < $required){
            $response = [];
        }else {

            //удаляем старое расписание
            $older = PersonalSchedule::all()->where('coach_id', $coach->id);

            foreach ($older as $item) {
                $item->delete();
            }

            $new = array();

            //добавляем новое
            foreach ($data as $item) {
                $new[] = ['day_id' => $item['day_id'],
                    'time_begin' => $item['time_begin'],
                    'coach_id' => $coach->id];
            }
            DB::table('personal_schedules')->insert($new);

            //меняем дату изменения расписания
            $time = TimeEditPersonalSchedule::all()->where('coach_id', $coach->id)->first();
            $time->date_edit = date("Y-m-d");
            $time->save();

            //отправляем новое расписание
            $response = PersonalSchedule::with("day")->where('coach_id', $coach->id)->get();
        }

        return response()->json($response);
    }
}
