<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Utils;
use App\Models\Customer;
use App\Models\GroupWorkout;
use App\Models\Image;
use App\Models\LimitedSubscription;
use App\Models\PersonalSchedule;
use App\Models\SignUpGroupWorkout;
use App\Models\SignUpPersonalWorkout;
use App\Models\UnlimitedSubscription;
use App\Models\User;
use App\Notifications\userNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    // получить клиента из авторизированного пользователя
    private function getCustomer()
    {
        $user = User::with('customer')->where('email', auth()->user()->email)->first();
        return Customer::with('user')->where('user_id', $user->id)->first();
    }

    //получает информацию о текущем абонементе (безлимит)
    public function aboutSubscription(): JsonResponse
    {
        $customer = $this->getCustomer();

        $subscription = UnlimitedSubscription::with('unlimited_price_list.subscription_type')
            ->where('customer_id', $customer->id)
            ->orderByDesc('open')
            ->first();

        return response()->json($subscription);
    }

    //получает информацию о текущем абонементе (тренировки с тренером)
    public function aboutSubscriptionWithCoach(): JsonResponse
    {
        $customer = $this->getCustomer();

        $subscription = LimitedSubscription::with('limited_price_list.coach')
            ->where('customer_id', $customer->id)
            ->orderByDesc('open')
            ->first();

        return response()->json($subscription);
    }

    //проверяем клиента
    private function checkingCustomer(): int
    {
        $customer = $this->getCustomer();

        $code = 0;

        //проверяем клиента
        //получаем абонемент клиента
        $subscription = UnlimitedSubscription::with('unlimited_price_list.subscription_type')
            ->where('customer_id', $customer->id)
            ->orderByDesc('open')
            ->first();

        //находим дату окончания действия абонемента
        $date = Utils::incMonths($subscription->open, $subscription->unlimited_price_list->validity_period);

        //не может записаться на групповые тренировки если:
        //1. нет действующего абонемента
        if ($date <= date("Y-m-d")) {
            $code = 1;
        }

        //2. в тариф абонемента не входят групповые тренировки
        if ($subscription->unlimited_price_list->subscription_type->group == 0) {
            $code = 2;
        }

        return $code;
    }

    public function checkingCustomerForGate(): bool
    {
        $code = $this->checkingCustomer();
        return ($code === 1 || $code === 2);
    }

    public function checkingCustomerShowError(): string
    {
        return match ($this->checkingCustomer()) {
            1 => "У вашего абонемента закончился срок действия!",
            2 => "В ваш тариф не входят групповые тренировки!",
            default => "ok",
        };
    }

    // получить все доступные тренировки для записи клиента
    public function getAvailableWorkouts(): JsonResponse
    {
        /*
        if (Gate::allows('checking-the-subscription')) {
            return response()->json($this->checkingCustomerShowError());
        } */

        $msg = $this->checkingCustomerShowError();

        if ($msg !== "ok") {
            return response()->json($msg);
        }

        $customer = $this->getCustomer();
        return response()->json(Utils::getAvailableWorkouts($customer));
    }

    // получить все актуальные записи клиента (на которые клиент может прийти)
    public function currentSignUp(): JsonResponse
    {
        $customer = $this->getCustomer();

        $signUpWorkouts = array();

        $workouts_id = SignUpGroupWorkout::all()
            ->where('customer_id', $customer->id)
            ->pluck('group_workout_id');

        $workouts = GroupWorkout::with('schedule.gym', 'schedule.workout_type', 'schedule.coach', 'schedule.day')
            ->whereIn('id', $workouts_id)
            ->where('event', '>=', date("Y-m-d"))->get();

        foreach ($workouts as $workout) {
            if ($workout->event == date("Y-m-d")) {
                if ($workout->schedule->time_begin > date("H:i:s")) {
                    $signUpWorkouts[] = $workout;
                }
            } else {
                $signUpWorkouts[] = $workout;
            }
        }

        return response()->json($signUpWorkouts);
    }

    // запись клиента на тренировки
    //человек может записаться максимум на 2 тренировки в день
    public function signUp(Request $request): JsonResponse
    {
        $id = $request->input('id'); // id тренировки
        $customer = $this->getCustomer();

        $workout = GroupWorkout::all()->where('id', $id)->first();

        //человек может записаться максимум на 2 тренировки в день
        $workouts_id = SignUpGroupWorkout::all()
            ->where('customer_id', $customer->id)
            ->pluck('group_workout_id');

        // находим кол-во тренировок на которые записан клиент
        $workouts = GroupWorkout::all()
            ->whereIn('id', $workouts_id)
            ->where('event', '=', $workout->event);

        foreach ($workouts as $workout) {
            $arr[] = $workout;
        }

        // если уже записан на 2 трен возвращаем их для возможности отмены
        if ($workouts->count() > 1) {
            return response()->json($arr);
        }

        $sign = new SignUpGroupWorkout();
        $sign->customer_id = $customer->id;
        $sign->group_workout_id = $id;

        $sign->save();

        return response()->json($sign);
    }

    //отмена записи на групповую тренировку
    public function deleteSignUpGroupWorkout(Request $request): JsonResponse
    {
        $id = $request->input('id'); // id тренировки
        $customer = $this->getCustomer();

        //нашли запись на тренировку которую будем удалять
        $sign = SignUpGroupWorkout::all()
            ->where('customer_id', $customer->id)
            ->where('group_workout_id', $id)->first();

        $sign->delete();

        return response()->json($sign);
    }

    // получить все доступные персональные тренировки для записи клиента
    // валидация - можно записаться на тренировку если в абонементе еще есть тренировки
    public function getPersonalWorkouts(): JsonResponse
    {
        $response['status'] = "";
        $response['answer'] = null;
        $response['message'] = "";

        $customer = $this->getCustomer();

        // Проверить срок действия абонемента
        // получаем последний оформленный абонемент клиента
        $subscription = LimitedSubscription::with('limited_price_list')
            ->where('customer_id', $customer->id)
            ->orderByDesc('open')
            ->first();

        if ($subscription !== null) {
            //находим дату окончания действия абонемента
            $date = Utils::incMonths($subscription->open, 1);

            // Проверить кол-во доступных тренировок в абонементе абонемента
            $count = $subscription->limited_price_list->amount_workout;

            $workouts = SignUpPersonalWorkout::all()
                ->where('customer_id', $customer->id)
                ->where('date_begin', '>=', $subscription->open);

            // нет действующего абонемента
            if ($date <= date("Y-m-d")) {
                $response['status'] = "failed";
                $response['answer'] = null;
                $response['message'] = "У вашего абонемента закончился срок действия!";
            }

            // клиент выходил все тренировки в абонементе
            if ($workouts->count() >= $count) {
                $response['status'] = "failed";
                $response['answer'] = null;
                $response['message'] = "У вашего абонемента закончились тренировки!";
            }

            if ($response['status'] !== "failed") {

                // Найти расписание тренера
                $schedule = PersonalSchedule::all()
                    ->where('coach_id', $subscription->limited_price_list->coach_id)
                    ->pluck('id');

                // Получить тренировки на которые еще никто не записан
                $answer = SignUpPersonalWorkout::with('schedule.day')
                    ->where('date_begin', '>', date("Y-m-d"))
                    ->whereIn('schedule_id', $schedule)
                    ->where('customer_id', null)
                    ->get();

                $response['status'] = "success";
                $response['answer'] = $answer;
                $response['message'] = "Тренировки получены!";
            }

        } else {
            $response['status'] = "failed";
            $response['answer'] = null;
            $response['message'] = "У ваc не оформлен абонемент!";
        }

        return response()->json($response);
    }

    // записаться на персональную тренировку
    // Валидация - можно записаться только на 1 тренировку в день
    public function singUpForPersonalWorkout(Request $request): JsonResponse
    {

        $response = [];

        $id = $request->input('id'); // id тренировки
        $customer = $this->getCustomer();

        // находим тренировку для записи
        $workout = SignUpPersonalWorkout::with('schedule.day')->where('id', $id)->first();

        // можно записаться только на 1 тренировку в день
        $sign = SignUpPersonalWorkout::all()->where('customer_id', $customer->id)
            ->where('date_begin', $workout->date_begin)->first();

        if ($sign !== null) {
            $response['status'] = "failed";
            $response['answer'] = null;
            $response['message'] = "Уже была сделана запись на тренировку на этот день!";

        } else {

            $count = $this->aboutAmountWorkouts()->original['count'];;

            if ($count > 0) {
                //валидация пройдена - делаем запись
                $workout->customer_id = $customer->id;
                $workout->save();

                // отсылаем уведомление тренеру о записи клиента
                $user = $workout->schedule->coach->user;

                $data = date( 'd.m.Y', strtotime($workout->date_begin));
                $time = substr($workout->schedule->time_begin,0,-3);

                $message = "Была сделана запись на персональную тренировку на {$data} число, начало в {$time}.";

                Notification::send($user, new userNotification(
                    $message,
                    $workout->customer->user->id
                ));

                $response['status'] = "success";
                $response['answer'] = $workout;
                $response['message'] = "Регистрация на тренировку успешно завершена!";
            } else {
                $response['status'] = "failed";
                $response['answer'] = null;
                $response['message'] = "В абонементе нет больше тренировок!";
            }
        }

        return response()->json($response);
    }

    // получить свои актуальные записи на персональные тренировки
    public function getSingUpForPersonalWorkout(): JsonResponse
    {

        $customer = $this->getCustomer();

        $signUpWorkouts = array();

        $workouts = SignUpPersonalWorkout::with('schedule.day')->where('customer_id', $customer->id)
            ->where('date_begin', '>=', date("Y-m-d"))->get();

        foreach ($workouts as $workout) {
            if ($workout->date_begin == date("Y-m-d")) {
                if ($workout->schedule->time_begin > date("H:i:s")) {
                    $signUpWorkouts[] = $workout;
                }
            } else {
                $signUpWorkouts[] = $workout;
            }
        }

        return response()->json($signUpWorkouts);
    }

    //отмена записи на персональную тренировку
    //Валидация - можно отменить тренировку за 2 дня до ее начала
    public function deleteSingUpForPersonalWorkout(Request $request): JsonResponse
    {

        $id = $request->input('id'); // id тренировки

        //Валидация - можно отменить тренировку за 2 дня до ее начала
        $workout = SignUpPersonalWorkout::with('schedule.day', 'customer.user.image')->where('id', $id)->first();

        $max = date("Y-m-d", strtotime("+1 days", strtotime(date("Y-m-d"))));

        if ($workout->date_begin <= $max) {
            $response['status'] = "failed";
            $response['answer'] = null;
            $response['message'] = "Нельзя отменить тренировку, если до ее проведения осталось менее 2 дней!";
        } else {

            $sender = $workout->customer->user->id;

            // изменяем запись на персональную тренировку
            $workout->customer_id = null;
            $workout->save();

            // отсылаем уведомление тренеру о записи клиента
            $user = $workout->schedule->coach->user;

            $data = date( 'd.m.Y', strtotime($workout->date_begin));
            $time = substr($workout->schedule->time_begin,0,-3);

            $message = "Была отменена запись на персональную тренировку на {$data} число, начало в {$time}.";

            Notification::send($user, new userNotification($message, $sender));

            $response['status'] = "success";
            $response['answer'] = $workout;
            $response['message'] = "Тренировка успешно отменена!";
        }

        return response()->json($response);
    }

    // узнать количество тренировок в абонементе
    public function aboutAmountWorkouts(): JsonResponse
    {
        $customer = $this->getCustomer();

        // получаем последний оформленный абонемент клиента
        $subscription = LimitedSubscription::with('limited_price_list')
            ->where('customer_id', $customer->id)
            ->orderByDesc('open')
            ->first();

        // Проверить кол-во доступных тренировок в абонементе абонемента
        $count = $subscription->limited_price_list->amount_workout;

        $workouts = SignUpPersonalWorkout::all()
            ->where('customer_id', $customer->id)
            ->where('date_begin', '>=', $subscription->open)->count();

        return response()->json([
            'count' => $count - $workouts
        ]);
    }

}
