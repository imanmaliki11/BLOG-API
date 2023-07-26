<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Throwable;

class AuthController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function register(Request $req) {
        try {
            $rules = [
                "name" => "required|max:30|min:3",
                "email" => "required|email:dns|unique:users,email",
                "password" => "required|min:8|max:15",
                "webname" => "required|max:30|min:3"
            ];

            $validator = Validator::make($req->json()->all(), $rules);
            if($validator->fails()) {
                return response([
                    "status" => false,
                    "message" => $validator->errors()->first()
                ], 400);
            }

            $data = $validator->safe()->only(["name", "email", "password", "webname"]);
            $user = new User($data);
            $user->password = Hash::make($req->json('password'));
            $user->save();

            return response([
                "status" => true,
                "message" => "Success add User",
                "data" => $user
            ], 201);

        } catch (Throwable $t) {
            return response([
                "status" => false,
                "message" => "Server error.",
                "dev-msg" => $t->getMessage()
            ], 500);
        }
    }

    public function login(Request $req) {
        try {
            $rules = [
                "email" => "required|email:dns",
                "password" => "required|max:15|min:8"
            ];

            $validator = Validator::make($req->json()->all(), $rules);
            if($validator->fails()) {
                return response([
                    "status" => false,
                    "message" => $validator->errors()->first()
                ], 400);
            }

            $findUser = User::where("email", $req->json("email"))->first();
            if(!$findUser) {
                return response([
                    "status" => false,
                    "message" => "Invalid email"
                ], 400);
            }

            if(Hash::check($req->json("password"), $findUser->password)) {
                $token = Str::random(40);
                $findUser->token = $token;
                $findUser->update();

                return response([
                    "status" => true,
                    "message" => "Login success",
                    "data" => $findUser,
                    "remember_token" => $token
                ], 200);
            } else {
                return response([
                    "status" => false,
                    "message" => "Invalid email"
                ], 400);
            }
        } catch (Throwable $t) {
            return response([
                "status" => false,
                "message" => "Server error.",
                "dev-msg" => $t->getMessage()
            ], 500);
        }
    }

    //
}