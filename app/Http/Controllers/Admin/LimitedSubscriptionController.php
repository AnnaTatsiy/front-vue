<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coach;
use App\Models\Customer;
use App\Models\LimitedPriceList;
use App\Models\LimitedSubscription;
use App\Models\User;
use App\Notifications\userNotification;
use DateTime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;

class LimitedSubscriptionController extends Controller
{
    // получить все записи (вывести все подписки на тренировки с тренерами)
    public function getLimitedSubscriptions(): JsonResponse{
        return response()->json(LimitedSubscription::with('customer', 'limited_price_list.coach')->orderByDesc('open')->get());
    }

    // получить все записи (вывести все подписки на тренировки с тренерами) постранично
    public function limitedSubscriptions(): JsonResponse{
        return response()->json(LimitedSubscription::with('customer', 'limited_price_list.coach')->orderByDesc('open')->paginate(12));
    }

    //Сторона Администратора: купленные тренировки данного клиента.
    public function selectLimitedSubscriptionsByCustomer(Request $request): JsonResponse{
        $id = $request->input('customer');
        return response()->json(LimitedSubscription::with( 'limited_price_list', 'coach')->where('customer_id', '=', $id)->get());
    }

    //добавить подписку на персональные тренировки
    public static function addLimitedSubscription(Request $request): JsonResponse{
        $response = [];

        $coach = Coach::all()->where('passport',$request->input('coach'))->first();
        $customer = Customer::all()->where('passport',$request->input('customer'))->first();
        $amount_workout = $request->input('amount_workout');

        $response["status"] = "";
        $response['answer'] = null;
        $response["message"] = "Подписка не была добавлена";

        $response["errors"] = array(
            'coach' => "",
            'customer' => ""
        );

        if($customer === null){
            $response["errors"]['customer'] = "Нет клиента с данным номером-серии паспорта!";
            $response["status"] = "failed";
        }

        if ($coach === null){
            $response["errors"]['coach'] = "Нет тренера с данным номером-серии паспорта!";
            $response["status"] = "failed";
        }
        else {

            // нельзя купить абонемент у тренера, который запретил продажу
            if ($coach->sale === 0) {
                $response["errors"]['coach'] = "Этот тренер запретил продажу абонементов!";
                $response["status"] = "failed";
            }

            if ($coach->sale === 1 && $response["status"] !== "failed") { // продажа разрешена
                $limited_price_list = LimitedPriceList::all()->where('coach_id', $coach->id)
                    ->where('amount_workout', $amount_workout)->first();

                $sub = new LimitedSubscription();
                $sub->customer_id = $customer->id;
                $sub->limited_price_list_id = $limited_price_list->id;
                $sub->open = date_format(new DateTime(), 'Y-m-d');

                $sub->save();

                // уведомляем тренера об оформлении абонемента
                $user = $coach->user;

                $message = "Был оформлен абонемент на {$sub->limited_price_list->amount_workout} тренировок.";

                Notification::send($user, new userNotification(
                    $message,
                    $sub->customer->user->id,
                ));

                // Нужно проверить сколько теперь нужно тренировок в расписании тренера
                // если недостаточно, то уведомить администратора об этом
                $requiredAmountWorkouts = CoachController::requiredAmountWorkouts($coach->id);

                $admin = User::all()->where('role', 'admin');

                if(($requiredAmountWorkouts->original['fact'] < $requiredAmountWorkouts->original['required'])){
                    Notification::send($admin, new userNotification(
                        'У тренера слишком мало тренировок в расписании!',
                        $coach->user->id
                    ));

                } else{
                    if(($requiredAmountWorkouts->original['fact'] < $requiredAmountWorkouts->original['recommend'])){
                        Notification::send($admin, new userNotification(
                            'У тренера меньше рекомендованного количества тренировок в расписании!',
                            $coach->user->id
                        ));
                    }
                }

                $response["status"] = "success";
                $response["message"] = "Подписка успешно оформлена!";
                $response['answer'] = LimitedSubscription::with('customer', 'limited_price_list.coach')->where('id', $sub->id)->first();
            }
        }

        return response()->json($response);
    }
}
