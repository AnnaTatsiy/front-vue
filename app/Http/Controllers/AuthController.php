<?php

namespace App\Http\Controllers;

use App\Models\Coach;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function register(Request $request){
        return User::create([
            'email'=> $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'role' => $request->input('role')
        ]);

    }

    public function login(Request $request){

        if(!Auth::attempt($request->only('email', 'password'))){
            return response([
                'message' => "unauthorized"
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user =  Auth::user();
        $token = $user->createToken('token')->plainTextToken;

        $cookie = cookie('jwt', $token, 60*24);
        return response([
            'message' => $token
        ])->withCookie($cookie);
    }

    public function user()
    {
            $name = 'Администратор';
            $user = Auth::user();

            if($user->role === 'customer'){
                $customer = Customer::all()->where('user_id', $user->id)->first();
                $name = $customer->name.' '.$customer->patronymic;
            }

            if ($user->role === 'coach'){
                $coach = Coach::all()->where('user_id', $user->id)->first();
                $name = $coach->name.' '.$coach->patronymic;
            }

            return response([
                'name' => $name,
                'email' => $user->email,
                'role' => $user->role
            ]);
    }

    public function logout(){
        $cookie = Cookie::forget('jwt');

        return response([
            'message' => 'success'
        ])->withCookie($cookie);
    }
}
