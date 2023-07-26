<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Throwable;

class UserController extends Controller
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

    //Get User By TOKEN
    public function getUser() {
        return Auth::user();
    }

    //Get User By ID
    public function getUserByID($id) {
        $user = User::find($id);
        if($user) {
            return response([
                "status" => true,
                "message" => "Success get User",
                "data" => $user
            ]);
        } 
        return response([
            "status" => false,
            "message" => "Invalid User ID"
        ], 400);
    }

    //Update User By TOKEN
    public function updateUser(Request $req) {
        try {
            $rules = [
                "name" => "max:30|min:3",
                "webname" => "max:30|min:3"
            ];

            $validator = Validator::make($req->json()->all(), $rules);
            if($validator->fails()) {
                return response([
                    "status" => false,
                    "message" => $validator->errors()->first()
                ], 400);
            }

            $data = $validator->safe()->only(["name", "webname"]);
            $user = Auth::user();
            $user->update($data);

            return response([
                "status" => true,
                "message" => "Success Update User",
                "data" => $user
            ], 200);

        } catch (Throwable $t) {
            return response([
                "status" => false,
                "message" => "Server error.",
                "dev-msg" => $t->getMessage()
            ], 500);
        }
    }

    //Upload Logo For Website
    public function uploadLogo(Request $req) {
        try {
            $validator = Validator::make($req->all(), [
                'logo' => [
                    'required',
                    File::image()
                        ->min(8)
                        ->max(3 * 1024)
                        ->dimensions(Rule::dimensions()->maxWidth(1200)->maxHeight(1200)),
                ],
            ]);
            if($validator->fails()) {
                return response([
                    "status" => false,
                    "message" => $validator->errors()->first()
                ], 400);
            }
    
            $user = Auth::user();
            
            if($user) {
                if($user->logo && file_exists("/home/mene1666/public_html/blog-api". str_replace(url(), "", $user->logo))) {
                    
                    unlink("/home/mene1666/public_html/blog-api" . str_replace(url(), "", $user->logo));
                }
                $logo = str_replace(" ", "_", str_replace(":", "", Str::random(10) . "_". Carbon::now())) . ".jpg";
                $req->file("logo")->move("/home/mene1666/public_html/blog-api/logo", $logo);
                $path = url() . "/logo" . "/" . $logo;
                $user->logo = $path;
                $user->save();
                return response([
                    "status" => false,
                    "message" => "Success upload logo",
                    "data" => $user
                ]);
            }
        } catch (Throwable $t) {
            return response([
                "status" => false,
                "message" => "Server error.",
                "dev-msg" => $t->getMessage()
            ], 500);
        }
    }

}
