<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Utils;
use App\Mail\user\Password;
use App\Models\Coach;
use App\Models\Customer;
use App\Models\LimitedPriceList;
use App\Models\LimitedSubscription;
use App\Models\UnlimitedPriceList;
use App\Models\UnlimitedSubscription;
use App\Models\User;
use Faker\Generator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    // получить все записи (вывод всех клиентов)
    public function customersAll(): JsonResponse
    {
        return response()->json(Customer::with('user')->get());
    }

    // получить все записи (вывод всех клиентов) постранично
    public function customers(): JsonResponse
    {
        return response()->json(Customer::with('user')->paginate(12));
    }

    // поиск клиента по серии-номеру паспорта
    public function getCustomersByPassport(Request $request): JsonResponse
    {
        return response()->json(Customer::all()->where('passport', $request->input('passport')));
    }

    // проверка: данных для добавления клиента
    public function checkDataForCustomerAdd(Request $request): JsonResponse
    {
        $response = [];

        // получаю поля из запроса
        $email = $request->input('mail');
        $passport = $request->input('passport');
        $number = $request->input('number');

        $response["status"] = "success";

        $response["errors"] = array(
            'mail' => "",
            'passport' => "",
            'number' => ""
        );

        if (!$this->checkingUniquePassport($passport)) {
            $response["errors"]['passport'] = "Уже был зарегистрирован клиент с данным номером-серии паспорта!";
            $response["status"] = "failed";
        }

        if (!$this->checkingUniqueMail($email)) {
            $response["errors"]['mail'] = "Уже был зарегистрирован клиент с данной почтой!";
            $response["status"] = "failed";
        }

        if (!$this->checkingUniqueNumber($number)) {
            $response["errors"]['number'] = "Уже был зарегистрирован клиент с данным номером телефона!";
            $response["status"] = "failed";
        }

        return response()->json($response);
    }

    // добавляем клиента и сразу оформляем абонемент
    public function addCustomer(Request $request): JsonResponse
    {
        $response = [];

        // получаю поля из запроса
        $surname = $request->input('surname');
        $name = $request->input('name');
        $patronymic = $request->input('patronymic');
        $email = $request->input('mail');
        $passport = $request->input('passport');
        $birth = $request->input('birth');
        $number = $request->input('number');
        $registration = $request->input('registration');

        $subscription_type = $request->input('subscription_type');
        $validity_period = $request->input('validity_period');

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
                "спасибо, что выбрали нас!"));

        $customer = new Customer();

        //сохраняю в БД
        $customer->surname = $surname;
        $customer->name = $name;
        $customer->patronymic = $patronymic;
        $customer->passport = $passport;
        $customer->birth = $birth;
        $customer->number = $number;
        $customer->registration = $registration;

        $user = User::create(
            [
                'email' => $email,
                'password' => bcrypt($password),
                'image_id' => 1,
                'role' => 'customer',
            ]
        );

        $customer->user_id = $user->id;

        $customer->save();

        // оформляем абонемент
        $unlimited_price_list = UnlimitedPriceList::all()->where('subscription_type_id', $subscription_type)
            ->where('validity_period', $validity_period)->first();

        $sub = new UnlimitedSubscription();
        $sub->customer_id = $customer->id;
        $sub->unlimited_price_list_id = $unlimited_price_list->id;
        $sub->open = date('Y-m-d');

        $sub->save();

        $response["message"] = "Клиент успешно оформлен!";
        $response['answer'] = Customer::with('user')->where('id', $customer->id)->first();

        return response()->json($response);
    }

    //редактирование клиента
    public function editCustomer(Request $request): JsonResponse
    {
        $response = [];

        // получаю поля из запроса
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
        $response["message"] = "Клиент не был редактирован";

        $response["errors"] = array(
            'mail' => "",
            'passport' => "",
            'number' => ""
        );

        //если редактирование, то поля паспорт, email и номер телефона могут не изменятся

        $customer = Customer::all()->where('id', $request->input('id'))->first();
        $user = User::all()->where('id', $customer->user_id)->first();

        $editPassport = $customer->passport === $passport;
        $editMail = $user->email === $email; //поля не изменились
        $editNumber = $customer->number === $number;


        if (!$this->checkingUniquePassport($passport) && !$editPassport) {
            $response["errors"]['passport'] = "Уже был зарегистрирован клиент с данным номером-серии паспорта!";
            $response["status"] = "failed";
        }

        if (!$this->checkingUniqueMail($email) && !$editMail) {
            $response["errors"]['mail'] = "Уже был зарегистрирован клиент с данной почтой!";
            $response["status"] = "failed";
        }

        if (!$this->checkingUniqueNumber($number) && !$editNumber) {
            $response["errors"]['number'] = "Уже был зарегистрирован пользователь с данным номером телефона!";
            $response["status"] = "failed";
        }

        //если валидация пройдена - добавляем/редактируем клиента
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
                    'редактирование данных завершено.'));

            $customer = Customer::all()->where('id', $request->input('id'))->first();

            //сохраняю в БД
            $customer->surname = $surname;
            $customer->name = $name;
            $customer->patronymic = $patronymic;
            $customer->passport = $passport;
            $customer->birth = $birth;
            $customer->number = $number;
            $customer->registration = $registration;

            $user->password = bcrypt($password);
            $user->email = $request->input('mail');
            $user->save();

            $customer->save();

            $response["status"] = "success";
            $response["message"] = "Клиент успешно редактирован!";
            $response['answer'] = Customer::with('user')->where('id', $customer->id)->first();
        }

        return response()->json($response);
    }

    //проверка данных на уникальность

    //проверка паспорта
    public function checkingUniquePassport($value): bool
    {
        return !count(Customer::all()->where('passport', $value));
    }

    //проверка номера телефона
    public function checkingUniqueNumber($value): bool
    {
        return !count(Customer::all()->where('number', $value));
    }

    //проверка email
    public function checkingUniqueMail($value): bool
    {
        return !count(User::all()->where('email', $value));
    }
}
