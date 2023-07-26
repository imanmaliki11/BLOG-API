<?php

namespace App\Http\Controllers;

use App\Models\Tags;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TagsController extends Controller
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

    public function createTags(Request $req) {
        $input = [
            "name" => $req->json("name"),
            "slug" => str_slug($req->json("name"))
        ];

        $validate = Validator::make($input, [
            "name" => "required|min:3|max:30",
            "slug" => "required|unique:tags,slug"
        ]);

        if($validate->fails()) {
            return response([
                "status" => false,
                "message" => $validate->errors()->first()
            ], 400);
        }

        $tags = new Tags($input);
        $tags->save();

        return response([
            "status" => true,
            "message" => "Success add Tags",
            "data" => $tags
        ], 201);
    }

    public function deleteTags($id) {
        $tags = Tags::find($id);
        if($tags) {
            $tags->delete();
            return response([
                "status" => true,
                "message" => "Success delete Tags",
                "data" => $tags
            ], 200);
        }

        return response([
            "status" => false,
            "message" => "Invalid tags ID"
        ], 400);
    }

    public function getTagsByID($id) {
        $tags = Tags::find($id);
        if($tags) {
            return response([
                "status" => true,
                "message" => "Success get Tags",
                "data" => $tags
            ], 200);
        }
        return response([
            "status" => false,
            "message" => "Invalid tags ID"
        ], 400);
    }

    public function getTags() {
        $tags = Tags::all();
        return response([
            "status" => true,
            "message" => "Success get all tags",
            "data" => $tags
        ]);
    }

    public function updateTags(Request $req, $id) {
        $tags = Tags::find($id);
        if($tags) {
            $slug = str_slug($req->json("name"));
            $findSlug = Tags::where("slug", $slug)->where("id", "!=", $tags->id)->first();
            if($findSlug) {
                return response([
                    "status" => false,
                    "message" => "The slug has already been taken."
                ], 400);
            }

            $tags->name = $req->json("name");
            $tags->slug = $slug;
            $tags->update();

            return response([
                "status" => true,
                "message" => "Success update Tags",
                "data" => $tags
            ], 200);
        }

        return response([
            "status" => false,
            "message" => "Invalid tags ID"
        ], 400);
    }

    //
}
