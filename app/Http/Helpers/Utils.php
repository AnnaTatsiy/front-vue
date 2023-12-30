<?php

namespace App\Http\Helpers;

use App\Http\Controllers\Customer\CustomerController;
use App\Models\Coach;
use App\Models\Customer;
use App\Models\GroupWorkout;
use App\Models\LimitedPriceList;
use App\Models\LimitedSubscription;
use App\Models\PersonalSchedule;
use App\Models\SignUpGroupWorkout;
use App\Models\SignUpPersonalWorkout;
use App\Models\UnlimitedPriceList;
use App\Models\UnlimitedSubscription;
use App\Models\User;
use Faker\Generator;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use function Symfony\Component\String\s;

class Utils
{

    //кол-во генерируемых записей в таблицах
    public static int $count_customers = 100;
    public static int $count_coaches = 20;
    public static int $count_gyms = 8;
    public static int $count_personal_schedules = 1500;

    //Отчества для фабрики клиенты и тренеры
    public static array $patronymic = ["Мирославов", "Константинов",
        "Тимофеев", "Владимиров", "Марков",
        "Ярославов", "Даниилов", "Давидов", "Ибрагимов",
        "Андреев", "Фёдоров", "Артёмов", "Александров", "Демидов",
        "Артёмов", "Давидов", "Арсентьев", "Маратов", "Даниилов",
        "Егоров", "Вадимов", "Сергеев"];

    // массив типы тренировок
    public static array $workout_types = [
        ['title' => "Аэробика"],
        ['title' => "Кикбоксинг"],
        ['title' => "Тай-бо"],
        ['title' => "Кангу Джампс"],
        ['title' => "Body Sculpt"],
        ['title' => "Йога"],
        ['title' => "Body Pump"],
        ['title' => "Круговой тренинг"],
        ['title' => "Тренировка с петлями"],
        ['title' => "Кроссфит"],
        ['title' => "Пилатес"],
        ['title' => "Зумба"],
        ['title' => "Стретчинг"],
        ['title' => "Бодифлекс"]
    ];

    //генератор случайной даты в диапазон
    public static function randomDate($start_date, $end_date): string
    {
        // Convert to timetamps
        $min = strtotime($start_date);
        $max = strtotime($end_date);

        // Generate random number using above bounds
        $val = rand($min, $max);

        // Convert back to desired date format
        return date('Y-m-d', $val);
    }

    //найти количество прошедших дней между двумя датами
    public static function subtractingDates($start, $end): int
    {

        $timeDiff = abs(strtotime($end) - strtotime($start));
        $numberDays = $timeDiff / 86400;  // 86400 seconds in one day

        // and you might want to convert to integer
        return intval($numberDays);
    }

    //генератор случайной даты в диапазон (первый параметр дата, второй секунды)
    public static function randomDateBySeconds($start_date, $max): string
    {
        // Convert to timestamps
        $min = strtotime($start_date);

        $val = rand($min, $min + $max);
        return date('Y-m-d', $val);
    }

    //Прибавить к дате месяцы
    public static function incMonths($start_date, $count): string
    {
        return date("Y-m-d", strtotime("+" . $count . " month", strtotime($start_date)));
    }

    //Отнять от даты месяцы
    public static function decMonths($end_date, $count): string
    {
        return date("Y-m-d", strtotime("-" . $count . " month", strtotime($end_date)));
    }

    // типы безлимит абонементов (добавление и изменение данных не будет)
    public static array $subscription_types = [
        ['title' => 'Простой', 'spa' => false, 'pool' => false, 'group' => false],
        ['title' => 'Простой+', 'spa' => false, 'pool' => false, 'group' => true],
        ['title' => 'Умный', 'spa' => false, 'pool' => true, 'group' => true],
        ['title' => 'Все включено', 'spa' => true, 'pool' => true, 'group' => true]
    ];

    public static array $getDayOfWeekById = [
        'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'
    ];

    //регистрирует клинта на тренировки с тренером
    public static function singUpPersonalWorkout(&$arr_sing_personal, $faker, $date, $customer_id, $limited_price_list_id): void
    {

        // вытаскиваю тип абонемента клиента
        $limited_price_list = LimitedPriceList::all()->where('id', $limited_price_list_id)->first();
        // находим нужные записи в рвсписании
        $personal_workout = PersonalSchedule::all()->where('coach_id', $limited_price_list->coach_id)->pluck('id');

        // записываем клиента на 8 персональных тренировок
        for ($j = 1; $j <= 8; $j++) {

            //клиент может купить перс тренировки на месяц, поэтому генерирую дату от начало открытия абонемента + месяц
            $arr_sing_personal[] = [
                'date_begin' => Utils::randomDateBySeconds($date, 2419200),
                'customer_id' => $customer_id, // клиент
                'schedule_id' => $personal_workout[$faker->numberBetween(0, count($personal_workout) - 1)]
            ];
        }
    }

    public static function getCountDaysForAdditionWorkouts($first, $now, $max_date): int
    {

        $count = 0;

        switch (true) {

            // первый запуск
            case $first:
                $count = 15;
                break;

            // если дата текущая больше даты последней тренировки
            case ($now > $max_date):
                $count = 14 + Utils::subtractingDates($max_date, $now);
                break;

            case ($now == $max_date):
                $count = 14;
                break;

            // если дата текущая меньше даты последней тренировки
            case ($now < $max_date):
                $count = 14 - Utils::subtractingDates($now, $max_date);
                break;
        }

        return $count;
    }

    // прибавляем 1 час 30 минут к времени
    public static function incTime($time): string
    {
        return date("H:i", strtotime('+30 minutes', strtotime('+1 hours', strtotime($time))));
    }

    // Replace last occurrence of a string in a string
    public static function strLeftReplace($search, $replace, $subject)
    {
        $pos = strrpos($subject, $search);

        if ($pos !== false) {
            $subject = substr_replace($subject, $replace, $pos, strlen($search));
        }

        return $subject;
    }

    // имитация работы фитнес клуба

    // клиенты оформляют абонементы безлимитные и абонементы на персональные тренировки
    // -- можно оформить абонемент если нет действующего абонемента
    public static function subscriptionsSeeder(): void
    {
        // цикл по клиентам
        $customers = Customer::all();

        foreach ($customers as $customer) {
            // проверяем есть ли абонемент у клиента
            $sub = UnlimitedSubscription::with('unlimited_price_list')
                ->where('customer_id', $customer->id)
                ->orderByDesc('open')
                ->first();

            if ($sub !== null) {
                $date = Utils::incMonths($sub->open, $sub->unlimited_price_list->validity_period);

                // если нет действующего абонемента - создаем его
                if ($date <= date("Y-m-d")) {

                    // случайно выбираю тип безлимит абонемента

                    UnlimitedSubscription::create([
                        'customer_id' => $customer->id,
                        'unlimited_price_list_id' => UnlimitedPriceList::all()->random(5)->first()->id,
                        'open' => date("Y-m-d")
                    ]);
                }
            }

            // добавляем абонементы с тренером
            // проверяем есть ли абонемент у клиента
            $sub = LimitedSubscription::with('limited_price_list')
                ->where('customer_id', $customer->id)
                ->orderByDesc('open')
                ->first();

            if ($sub !== null) {
                $date = Utils::incMonths($sub->open, 1);

                // если нет действующего абонемента - создаем его
                if ($date <= date("Y-m-d")) {

                    // случайно выбираю тип абонемента
                    LimitedSubscription::create([
                        'customer_id' => $customer->id,
                        'limited_price_list_id' => LimitedPriceList::all()->random(5)->first()->id,
                        'open' => date("Y-m-d")
                    ]);

                }
            }

        }
    }

    // записи клиентов на групповые и индивидуальные тренировки

    // получить доступные для записи тренировки и сделать запись

    // делаем записи на групповые тренировки каждого клиента,
    // который проходит валидацию
    public static function signUpGroupWorkoutSeeder(): void
    {
        // цикл по клиентам
        $customers = Customer::all();

        foreach ($customers as $customer) {

            //проверяем клиента
            //получаем абонемент клиента
            $subscription = UnlimitedSubscription::with('unlimited_price_list.subscription_type')
                ->where('customer_id', $customer->id)
                ->orderByDesc('open')
                ->first();

            if ($subscription !== null) {
                //находим дату окончания действия абонемента
                $date = Utils::incMonths($subscription->open, $subscription->unlimited_price_list->validity_period);

                // Не может записаться на групповые тренировки если:
                // 1. нет действующего абонемента
                // 2. в тариф абонемента не входят групповые тренировки
                if ($date > date("Y-m-d") && $subscription->unlimited_price_list->subscription_type->group !== 0) {

                    // получили доступные тренировки для записи
                    $availableWorkouts = Utils::getAvailableWorkouts($customer);

                    // записываем клиента на тренировки
                    // валидация:
                    // между тренировками должно быть не менее 2 дней

                    // сортируем массив по дате
                    usort($availableWorkouts, fn($a, $b) => $a->event < $b->event);

                    // цикл по доступным тренировкам
                    foreach ($availableWorkouts as $workout) {

                        // валидация: между тренировками должно быть не менее 2 дней
                        $date = $workout->event;

                        //прибавляем и отнимаем 2 дня
                        $max = date("Y-m-d", strtotime("+3 days", strtotime($date)));
                        $min = date("Y-m-d", strtotime("-3 days", strtotime($date)));

                        //находим тренировки по неподходящим датам
                        $workouts = GroupWorkout::all()->where('event', '<', $max)->where('event', '>', $min)->pluck('id');

                        // если записи клиента на неподходящие тренировки
                        $count = SignUpGroupWorkout::all()->where('customer_id', $customer->id)
                            ->whereIn('group_workout_id', $workouts)->count();

                        if ($count === 0) {
                            // создаем запись на персональную тренировку
                            SignUpGroupWorkout::create([
                                'customer_id' => $customer->id,
                                'group_workout_id' => $workout->id
                            ]);
                        }
                    }
                }
            }
        }
    }

    // делаем записи на персональные тренировки каждого клиента,
    // который проходит валидацию
    public static function signUpPersonalWorkoutsSeeder(): void
    {
        // цикл по клиентам
        $customers = Customer::all();

        foreach ($customers as $customer) {

            //проверяем клиента
            //получаем абонемент клиента
            $subscription = LimitedSubscription::with('limited_price_list')
                ->where('customer_id', $customer->id)
                ->orderByDesc('open')
                ->first();

            if ($subscription !== null) {
                //находим дату окончания действия абонемента
                $date = Utils::incMonths($subscription->open, 1);

                // Не может записаться на персональные тренировки если нет действующего абонемента
                if ($date > date("Y-m-d")) {

                    // Проверить кол-во доступных тренировок в абонементе абонемента
                    $count = $subscription->limited_price_list->amount_workout;

                    // сколько тренировок уже выходил клиент
                    $workouts = SignUpPersonalWorkout::all()
                        ->where('customer_id', $customer->id)
                        ->where('date_begin', '>=', $subscription->open);

                    // если у клиента есть тренировки в абонементе
                    if ($workouts->count() < $count) {

                        // Найти расписание тренера
                        $schedule = PersonalSchedule::all()
                            ->where('coach_id', $subscription->limited_price_list->coach_id)
                            ->pluck('id');

                        // Получить тренировки на которые еще никто не записан
                        $availableWorkouts = SignUpPersonalWorkout::all()
                            ->where('date_begin', '>', date("Y-m-d"))
                            ->whereIn('schedule_id', $schedule)
                            ->where('customer_id', null)
                            ->sort(fn($a, $b) => $a->date_begin < $b->date_begin);

                        // записываем клиента на тренировки
                        // между тренировками должно быть не менее 2 дней

                        // цикл по доступным тренировкам
                        foreach ($availableWorkouts as $workout) {

                            // валидация: между тренировками должно быть не менее 2 дней
                            $date = $workout->date_begin;

                            //прибавляем и отнимаем 2 дня
                            $max = date("Y-m-d", strtotime("+3 days", strtotime($date)));
                            $min = date("Y-m-d", strtotime("-3 days", strtotime($date)));

                            //находим тренировки по неподходящим датам
                            $count = SignUpPersonalWorkout::all()
                                ->where('date_begin', '<', $max)
                                ->where('date_begin', '>', $min)
                                ->where('customer_id', $customer->id)->count();

                            if ($count === 0) {
                                // записываем клиента на персональную тренировку
                                $workout->update(['customer_id' => $customer->id]);
                            }
                        }
                    }
                }
            }
        }
    }

    // получить все доступные тренировки для записи клиента
    public static function getAvailableWorkouts($customer): array
    {

        $availableWorkouts_temp = array();
        $availableWorkouts = array();

        // запись на тренировку должна быть активна
        $workouts = GroupWorkout::with('schedule.gym', 'schedule.workout_type', 'schedule.coach', 'schedule.day')
            ->where('cancelled', 0)->get();

        //клиент не может сделать запись на тренировку второй раз
        foreach ($workouts as $workout) {
            $count = SignUpGroupWorkout::all()
                ->where('group_workout_id', $workout->id)
                ->where('customer_id', $customer->id)
                ->count();

            if ($count == 0) {
                $availableWorkouts_temp[] = $workout;
            }
        }

        // на тренировку должно быть записано не более 20 человек
        foreach ($availableWorkouts_temp as $workout) {
            $count = SignUpGroupWorkout::all()
                ->where('group_workout_id', $workout->id)
                ->count();

            if ($count < 20) {
                $availableWorkouts[] = $workout;
            }
        }

        return $availableWorkouts;
    }

    public static function getUserByRole(User $user): array
    {
        $sender = [
            "fullName" => 'Администратор',
            "image"=> 'default.png'
        ];

        switch ($user->role){
            case 'customer':
                $customer = Customer::all()->where('user_id', $user->id)->first();
                $sender = [
                    "fullName" => $customer->surname.' '.$customer->name,
                    "image"=> $user->image->path
                ];
                break;
            case 'coach':
                $coach = Coach::all()->where('user_id', $user->id)->first();
                $sender = [
                    "fullName" => $coach->surname.' '.$coach->name,
                    "image"=> $user->image->path
                ];
                break;
        }

        return $sender;
    }

    public static function getNotificationsWithSender($notifications): array
    {
        $response = [];

        foreach ($notifications as $notification) {

            $id = $notification->data['sender'];
            $user = User::all()->where('id', $id)->first();
            $sender = Utils::getUserByRole($user);

            $response[] = [
                "id"=>$notification->id,
                "message" => $notification->data['message'],
                "sender" => $sender,
                "created_at"=>$notification->created_at,
            ];
        }

        return $response;
    }


}



