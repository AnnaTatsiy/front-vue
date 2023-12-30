<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Utils;
use App\Mail\user\Password;
use App\Models\Coach;
use App\Models\LimitedPriceList;
use App\Models\LimitedSubscription;
use App\Models\PersonalSchedule;
use App\Models\Schedule;
use App\Models\TimeEditPersonalSchedule;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CoachController extends Controller
{

    // получить все записи (вывод всех тренеров)
    public function coachesAll(): JsonResponse
    {
        return response()->json(Coach::with('user')->get());
    }

    // получить все записи (вывод всех тренеров) постранично
    public function coaches(): JsonResponse
    {
        return response()->json(Coach::with('user')->paginate(12));
    }

    //добавление/редактирование тренера
    // генерируем пароль каждый раз при редактировании
    public function decorCoach(Request $request): JsonResponse
    {
        $response = [];

        // получаю поля из запроса
        $isAdd = $request->input('isAdd'); //признак добавления
        $surname = $request->input('surname');
        $name = $request->input('name');
        $patronymic = $request->input('patronymic');
        $email = $request->input('mail');
        $passport = $request->input('passport');
        $birth = $request->input('birth');
        $number = $request->input('number');
        $registration = $request->input('registration');

        $response["status"] = "";
        $response['answer'] = null;
        $response["message"] = ($isAdd) ? "Тренер не был добавлен" : "Тренер не был редактирован" ;

        $response["errors"] = array(
            'mail' => "",
            'passport' => "",
            'number' => ""
        );

        $editPassport = false;
        $editMail = false;
        $editNumber = false;

        //если редактирование, то поля паспорт, email и номер телефона могут не изменятся
        if (!$isAdd) {
            $coach = Coach::all()->where('id', $request->input('id'))->first();
            $user = User::all()->where('id', $coach->user_id)->first();

            $editPassport = $coach->passport === $passport;
            $editMail = $user->email === $email; //поля не изменились
            $editNumber = $coach->number === $number;
        }

        if (!$this->checkingUniquePassport($passport) && !$editPassport) {
            $response["errors"]['passport'] = "Уже был зарегистрирован тренер с данным номером-серии паспорта!";
            $response["status"] = "failed";
        }

        if (!$this->checkingUniqueMail($email) && !$editMail) {
            $response["errors"]['mail'] = "Уже был зарегистрирован тренер с данной почтой!";
            $response["status"] = "failed";
        }

        if (!$this->checkingUniqueNumber($number) && !$editNumber) {
            $response["errors"]['number'] = "Уже был зарегистрирован пользователь с данным номером телефона!";
            $response["status"] = "failed";
        }

        //если валидация пройдена - добавляем/редактируем тренера
        if ($response["status"] !== "failed") {

            // генерирую пароль
            $password = Str::random(8);

            //отсылаю пароль на почту
            Mail::to($email)
                ->send(new Password(
                    $surname,
                    $name,
                    $patronymic,
                    $password,
                    $birth,
                    $email,
                    $passport,
                    $number,
                    $registration,
                    ($isAdd) ?"добро пожаловать в нашу команду!":'редактирование данных завершено.'));

            //сохраняю в БД тренера
            $coach = ($isAdd) ? new Coach() : Coach::all()->where('id', $request->input('id'))->first();

            $coach->surname = $surname;
            $coach->name = $name;
            $coach->patronymic = $patronymic;
            $coach->passport = $passport;
            $coach->birth = $birth;
            $coach->number = $number;
            $coach->registration = $registration;

            if ($isAdd) {
                //если добавление - создаем новую учетную запись
                $user = User::create(
                    [
                        'email' => $email,
                        'password' => bcrypt($password),
                        'image_id' => 1,
                        'role' => 'coach',
                    ]
                );

                $coach->user_id = $user->id;
            } else {
                $user->password = bcrypt($password);
                $user->email = $request->input('mail');
                $user->save();
            }

            $coach->save();

            if ($isAdd) {
                // добавляем дату редактирования расписания
                TimeEditPersonalSchedule::create([
                    'date_edit' => date('Y-m-d'),
                    'coach_id' => $coach->id,
                ]);

                // создаю прайс-лист на индивидуальные тренировки
                LimitedPriceListController::addLimitedPriceList($coach->id);
            }

            $response["status"] = "success";
            $response["message"] = ($isAdd) ? "Тренер успешно оформлен!" : "Тренер успешно редактирован!";
            $response['answer'] = Coach::with('user')->where('id', $coach->id)->first();
        }

        return response()->json($response);
    }

    //проверка данных на уникальность

    //проверка паспорта
    public function checkingUniquePassport($value): bool
    {
        return !count(Coach::all()->where('passport', $value));
    }

    //проверка номера телефона
    public function checkingUniqueNumber($value): bool
    {
        return !count(Coach::all()->where('number', $value));
    }

    //проверка email
    public function checkingUniqueMail($value): bool
    {
        return !count(User::all()->where('email', $value));
    }

    //сколько тренировок нужно выставить тренеру в расписании(за неделю) и сколько он выставил
    public static function requiredAmountWorkouts($id): JsonResponse
    {
        $date = Utils::decMonths(date('Y-m-d'), 1);

        //находим id прайса на 12 тренировок
        $price_id_12 = LimitedPriceList::all()
            ->where('coach_id', $id)
            ->where('amount_workout', 12)->first()->id;

        //находим id прайса на 8 тренировок
        $price_id_8 = LimitedPriceList::all()
            ->where('coach_id', $id)
            ->where('amount_workout', 8)->first()->id;

        //Находим кол-во действующих абонементов
        $count_12 = LimitedSubscription::all()
            ->where('limited_price_list_id', $price_id_12)
            ->where('open', '>', $date)
            ->count();

        //Находим кол-во действующих абонементов на 8 тренировок
        $count_8 = LimitedSubscription::all()
            ->where('limited_price_list_id', $price_id_8)
            ->where('open', '>', $date)
            ->count();

        //фактически сколько тренировок в расписании у тренера
        $fact = PersonalSchedule::all()
            ->where('coach_id', $id)->count();

        //сколько необходимо тренировок в расписании у тренера
        $required = 3 * $count_12 + 2 * $count_8;

        //сколько рекомендовано тренировок в расписании у тренера (5 для запаса)
        $recommend = $required + 5;

        return response()->json([
            'fact' => $fact,
            'required' => $required,
            'recommend' => $recommend
        ]);
    }

    // Увольнение(удаление) тренера
    // - удаляем аккаунт тренера
    // - удаляем тренировки тренера в расписании
    // - удаляем прайс-лист тренера
    // - удаляем время изменения расписания
    // - удаляем персональное расписание
    // валидация: нельзя удалить тренера, у которого есть действующие абонементы
    public function deleteCoach(Request $request): JsonResponse {
        $response = [];

        $response["status"] = "failed";
        $response['answer'] = null;
        $response["message"] = "Ошибка при увольнении тренера";

        $id = +$request->input('id');

        // находим тренера для удаления
        $coach = Coach::all()->where('id', $id)->first();

        // если нашли
        if ($coach !== null) {

            // валидация
            $list_price_id = LimitedPriceList::all()->where('coach_id', $coach->id)->pluck('id');
            $subscriptions = LimitedSubscription::all()->whereIn('limited_price_list_id', $list_price_id);

            $count = 0;

            foreach ($subscriptions as $subscription){
                if(Utils::incMonths($subscription->open, 1) > date('Y-m-d')){
                    $count += 1;
                }
            }

            if($count <= 0) {

                // удаляем персональное расписание
                $personal_schedules = PersonalSchedule::all()->where('coach_id', $id);
                foreach ($personal_schedules as $workout) {
                    $workout->delete();
                }

                // удаляем время изменения расписания
                $times = TimeEditPersonalSchedule::all()->where('coach_id', $id);
                foreach ($times as $time) {
                    $time->delete();
                }

                //удаляем тренировки тренера в расписании
                $workouts = Schedule::all()->where('coach_id', $id);

                foreach ($workouts as $workout) {
                    $workout->delete();
                }

                //удаляем прайс-лист тренера
                $prices = LimitedPriceList::all()->where('coach_id', $id);
                foreach ($prices as $price) {
                    $price->delete();
                }

                //удаляем тренера
                $coach->delete();

                //удаляем аккаунт тренера
                $user = User::all()->where('id', $coach->user_id)->first();
                $user->delete();

                $response["status"] = "success";
                $response["message"] = "Увольнение тренера успешно завершено!";
                $response['answer'] = $coach;
            } else {
                $response["message"] = "Нельзя уволить тренера, который имеет действующие абонементы!";
            }
        }

        return response()->json($response);
    }

}
