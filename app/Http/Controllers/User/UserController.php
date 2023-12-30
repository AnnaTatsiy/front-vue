<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Utils;
use App\Models\Coach;
use App\Models\Customer;
use App\Models\Image;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Notification;

class UserController extends Controller
{
    // получить изображение
    public function getImage(): JsonResponse
    {
        $id = auth()->user()->image_id;
        $image = Image::all()->where('id', $id)->first();
        return response()->json(["status" => "success", "data" => $image]);
    }

    //получить изображение от пользователя
    public function upload(Request $request): JsonResponse
    {
        $response = [];

        $fileArray = array('image' => $request->only('image'));

        $validator = Validator::make($fileArray,
            [
                'image' => 'required',
                'image.*' => 'bail|required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
            ],
            [
                'image' => 'Изображение не было выбрано',
                'image.max' => "Изображение слишком большое",
                'image.mimes' => "Требуется расширение файла jpg, png, jpeg, gif, svg",
                'image.image' => "Требуется изображение c расширением jpg, png, jpeg, gif, svg"
            ]
        );

        if ($validator->fails()) {
            return response()->json(["status" => "failed", "message" => "Ошибка валидации", "errors" => $validator->errors()->messages()[$validator->errors()->keys()[0]][0]]);
        }

        if ($request->has('image')) {
            $image = $request->file('image');
            $filename = Str::random(32) . "." . $image->getClientOriginalExtension();
            $image->move('users/', $filename);

            $new_image = new Image();
            $new_image->path = $filename;
            $new_image->save();

            $user = auth()->user();
            $user->image_id = $new_image->id;
            $user->save();


            $response["status"] = "success";
            $response["message"] = "Изображение загружено!";
        } else {
            $response["status"] = "failed";
            $response["message"] = "Ошибка сервера: изображение не загружено.";
        }
        return response()->json($response);
    }

    // получить все уведомления авторизированного пользователя
    public function getAllNotifications() : JsonResponse {
        return response()->json(Utils::getNotificationsWithSender(auth()->user()->notifications()->get()));
    }

    // получить непрочитанные уведомления авторизированного пользователя
    public  function getUnreadNotifications() : JsonResponse {
        return response()->json(Utils::getNotificationsWithSender(auth()->user()->unreadNotifications()->get()));
    }

    // отметить сообщение как прочитанное (доступ по id)
    public  function markAsReadById(Request $request): JsonResponse{
        $id = $request->input('id');

        auth()->user()
            ->unreadNotifications
            ->when($id, function ($query) use ($request) {
                return $query->where('id', $request->input('id'));
            })
            ->markAsRead();

        return response()->json([
            'id' => $id
        ]);
    }

    // отметить все уведомления как прочитанные
    public function allMarkAsRead(): JsonResponse  {
        auth()->user()->unreadNotifications->markAsRead();
        return response()->json([]);
    }

    //получить количество непрочитанных уведомлений
    public  function getUnreadNotificationsCount() : JsonResponse {
        return response()->json(['count' => auth()->user()->unreadNotifications()->count()]);
    }

}
