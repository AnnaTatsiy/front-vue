<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\UnlimitedPriceList;
use App\Models\UnlimitedSubscription;
use DateTime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UnlimitedSubscriptionController extends Controller
{
    // получить все записи (вывести все подписки на безлимит абонемент)
    public function getAllUnlimitedSubscriptions(): JsonResponse{
        return response()->json(UnlimitedSubscription::with( 'unlimited_price_list.subscription_type', 'customer')->orderByDesc('open')->get());
    }

    // получить все записи (вывести все подписки на безлимит абонемент) постранично
    public function unlimitedSubscriptions(): JsonResponse{
        return response()->json(UnlimitedSubscription::with('unlimited_price_list.subscription_type', 'customer')->orderByDesc('open')->paginate(12));
    }

    //Сторона Администратора: безлимит абонементы данного клиента.
    public function selectUnlimitedSubscriptionsByCustomer(Request $request): JsonResponse{
        $id = $request->input('customer');
        return response()->json(UnlimitedSubscription::with( 'unlimited_price_list', 'subscription_type')->where('customer_id', '=', $id)->get());
    }

    // добавить абонемент
    public function addUnlimitedSubscription(Request $request): JsonResponse //: Response
    {
        $response = [];

        $subscription_type = $request->input('subscription_type');
        $validity_period = $request->input('validity_period');
        $customer = Customer::all()->where('passport',$request->input('customer'))->first();

        //Признак: оформлять вместе с абонементом подписку на групповые тренировки ?
        $isAddLimitedSubscription = $request->input('is_add_lim');

        $response["status"] = "";
        $response['answer'] = null;
        $response["message"] = "Абонемент не был оформлен";

        $response["errors"] = array(
            'coach' => "",
            'customer' => ""
        );

        if($isAddLimitedSubscription){
            $json = LimitedSubscriptionController::addLimitedSubscription($request);
            $response = $json->original;

        } else{
            if($customer === null){
                $response["errors"]['customer'] = "Нет клиента с данным номером-серии паспорта!";
                $response["status"] = "failed";
            }
        }

        if($response["status"] !== "failed") {

            $unlimited_price_list = UnlimitedPriceList::all()->where('subscription_type_id', $subscription_type)
                ->where('validity_period', $validity_period)->first();

            $sub = new UnlimitedSubscription();
            $sub->customer_id = $customer->id;
            $sub->unlimited_price_list_id = $unlimited_price_list->id;
            $sub->open = date( 'Y-m-d');

            $sub->save();

            $response["status"] = "success";
            $response["message"] = "Абонемент успешно оформлен!";
            $response['answer'] = UnlimitedSubscription::with( 'unlimited_price_list.subscription_type', 'customer')->where('id', $sub->id)->first();
        }

        return response()->json($response);
    }
}
